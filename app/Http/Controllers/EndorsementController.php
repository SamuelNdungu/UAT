<?php
namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Endorsement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EndorsementController extends Controller
{
    public function index($policyId)
    {
        $policy = Policy::findOrFail($policyId);
        $endorsements = $policy->endorsements()->latest()->get();
        return view('endorsements.index', compact('policy', 'endorsements'));
    }

    public function create($policyId)
    {
        $policy = Policy::findOrFail($policyId);

        if ($policy->isCancelled()) {
            return redirect()->route('policies.show', $policy->id)
                ->with('error', 'Cannot create endorsements for a canceled policy.');
        }

        return view('endorsements.create', compact('policy'));
    }

    public function store(Request $request)
    {
        // Sanitize numeric inputs server-side to handle formatted values (commas, NBSPs, Unicode minus)
        $numericFields = [
            'sum_insured', 'rate', 'commission_rate', 'wht', 'aa_charges', 'premium', 'commission',
            'net_premium', 'pvt', 'ppl', 'excess', 'courtesy_car', 's_duty', 't_levy', 'pcf_levy',
            'policy_charge', 'other_charges', 'road_rescue', 'paid_amount', 'balance', 'premium_impact'
        ];

        foreach ($numericFields as $nf) {
            if ($request->has($nf)) {
                $val = $request->input($nf);
                if (is_string($val)) {
                    // Normalize Unicode dash characters to ASCII hyphen
                    $val = preg_replace('/\p{Pd}/u', '-', $val);
                    // Remove any non-digit, non-dot, non-hyphen characters (commas, spaces, currency symbols)
                    $clean = preg_replace('/[^0-9.\-]+/u', '', $val);
                    // If empty after cleaning, set null so validation will treat as nullable
                    $request->merge([$nf => $clean === '' ? null : $clean]);
                }
            }
        }

        $validatedData = $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'type' => 'required|in:addition,deletion,cancellation',
            'reason' => 'nullable|string',
            'effective_date' => 'nullable|date',
            'sum_insured' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'commission_rate' => 'nullable|numeric',
            'wht' => 'nullable|numeric',
            'aa_charges' => 'nullable|numeric',
            'premium' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'net_premium' => 'nullable|numeric',
            'pvt' => 'nullable|numeric',
            'ppl' => 'nullable|numeric',
            'excess' => 'nullable|numeric',
            'courtesy_car' => 'nullable|numeric',
            's_duty' => 'nullable|numeric',
            't_levy' => 'nullable|numeric',
            'pcf_levy' => 'nullable|numeric',
            'policy_charge' => 'nullable|numeric',
            'other_charges' => 'nullable|numeric',
            'road_rescue' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
        ]);

        $policy = Policy::findOrFail($validatedData['policy_id']);

        // Use Schema to check columns. Do this early so conditional payload fields (e.g., user_id) can be correctly determined.
        // getColumnListing returns an array of column names
        $endorsementTableCols = \Illuminate\Support\Facades\Schema::getColumnListing('endorsements');

        // Build payload only for columns that actually exist in the endorsements table (to avoid undefined column errors)
        $payload = ['policy_id' => $validatedData['policy_id']];
        // set user_id only if the column exists in the endorsements table
        if (in_array('user_id', $endorsementTableCols) && auth()->check()) {
            $payload['user_id'] = auth()->id();
        }

        // map incoming fields to stored columns
        $fieldMap = [
            'type' => 'type',
            'endorsement_type' => 'endorsement_type',
            'reason' => 'reason',
            'description' => 'description',
            'effective_date' => 'effective_date',
            'premium_impact' => 'premium_impact',
            // deltas
            'sum_insured' => 'delta_sum_insured',
            'premium' => 'delta_premium',
            'commission' => 'delta_commission',
            'wht' => 'delta_wht',
            's_duty' => 'delta_s_duty',
            't_levy' => 'delta_t_levy',
            'pcf_levy' => 'delta_pcf_levy',
            'policy_charge' => 'delta_policy_charge',
            'aa_charges' => 'delta_aa_charges',
            'other_charges' => 'delta_other_charges',
            'net_premium' => 'delta_net_premium',
            'excess' => 'delta_excess',
            'courtesy_car' => 'delta_courtesy_car',
            'ppl' => 'delta_ppl',
            'road_rescue' => 'delta_road_rescue',
        ];

        foreach ($fieldMap as $inputKey => $colName) {
            if (isset($validatedData[$inputKey]) && in_array($colName, $endorsementTableCols)) {
                $payload[$colName] = $validatedData[$inputKey];
            }
        }

        // backward-compat: if incoming uses 'type' but table has 'endorsement_type', map it
        if (isset($validatedData['type']) && in_array('endorsement_type', $endorsementTableCols) && !isset($payload['endorsement_type'])) {
            $payload['endorsement_type'] = $validatedData['type'];
        }
        // and vice-versa: if incoming had 'endorsement_type' but table has 'type'
        if (isset($validatedData['type']) && in_array('type', $endorsementTableCols) && !isset($payload['type'])) {
            $payload['type'] = $validatedData['type'];
        }

        // map reason -> description for older schema if needed
        if (isset($validatedData['reason']) && in_array('description', $endorsementTableCols) && !isset($payload['description'])) {
            $payload['description'] = $validatedData['reason'];
        }

        // If type is 'deletion' and user provided positive deltas, convert them to negative values
        $type = $validatedData['type'] ?? null;
        if (in_array($type, ['deletion', 'cancellation'])) {
            // Normalize sign for any delta_* keys present in payload
            foreach ($payload as $col => $val) {
                if (strpos($col, 'delta_') === 0 && is_numeric($val) && $val > 0) {
                    $payload[$col] = -$val;
                }
            }
        }

        // Handle cancellation defaulting: if the endorsements table stores deltas and user selected cancellation, set deltas = negative current policy values
        if (($validatedData['type'] ?? null) === 'cancellation') {
            foreach ($fieldMap as $inputKey => $colName) {
                if (strpos($colName, 'delta_') === 0 && in_array($colName, $endorsementTableCols)) {
                    // compute delta as negative of current policy value if not provided
                    if (!isset($payload[$colName])) {
                        $policyField = str_replace('delta_', '', $colName);
                        $payload[$colName] = -($policy->{$policyField} ?? 0);
                    }
                }
            }
            // also set typed fields where present
            if (in_array('type', $endorsementTableCols)) $payload['type'] = 'cancellation';
            if (in_array('reason', $endorsementTableCols) && !empty($validatedData['reason'])) $payload['reason'] = $validatedData['reason'];
            if (in_array('description', $endorsementTableCols) && !empty($validatedData['description'])) $payload['description'] = $validatedData['description'];
            if (in_array('premium_impact', $endorsementTableCols) && !isset($payload['premium_impact'])) {
                $payload['premium_impact'] = -($policy->gross_premium ?? 0);
            }
        }

        // Ensure premium_impact is not null (DB requires it)
        if (!isset($payload['premium_impact']) || is_null($payload['premium_impact'])) {
            // Prefer validated net_premium / premium (user inputs), then payload delta_net_premium / delta_premium, else 0
            $payload['premium_impact'] = $validatedData['net_premium'] ?? $validatedData['premium'] ?? $payload['delta_net_premium'] ?? $payload['delta_premium'] ?? 0;
        }

        // Create endorsement
        $endorsement = Endorsement::create($payload);

        // Update policy totals using the endorsement delta_* columns (not endorsement->sum_insured etc.)
        $policy->update([
            'sum_insured' => $policy->sum_insured + ($endorsement->delta_sum_insured ?? 0),
            'premium'     => $policy->premium     + ($endorsement->delta_premium ?? 0),
            'commission'  => $policy->commission  + ($endorsement->delta_commission ?? 0),
            'net_premium' => $policy->net_premium + ($endorsement->delta_net_premium ?? 0),
            'balance'     => $policy->balance     + ($endorsement->delta_balance ?? 0), // delta_balance if present
        ]);

        if ($validatedData['type'] === 'cancellation') {
            // Use canonical status spelling when updating
            $policy->update(['status' => 'canceled']);
        }

        return redirect()->route('policies.show', $policy->id)->with('success', 'Endorsement created successfully.');
    }

    public function show($policyId, $endorsementId)
    {
        $policy = Policy::findOrFail($policyId);
        $endorsement = $policy->endorsements()->findOrFail($endorsementId);
        return view('endorsements.show', compact('policy', 'endorsement'));
    }

    /**
     * Print a Deletion endorsement (credit note layout same as cancellation).
     */
    public function printDeletion($policyId, $endorsementId)
    {
        $policy = Policy::findOrFail($policyId);
        $endorsement = $policy->endorsements()->findOrFail($endorsementId);

        // Optionally ensure the endorsement is of type deletion (soft check)
        // if ($endorsement->type !== 'deletion') { abort(400, 'Endorsement is not a deletion.'); }

        return $this->downloadEndorsementPdf($policy, $endorsement, 'deletion');
    }

    /**
     * Print an Addition endorsement (use same layout as cancellation/credit note or create separate view later).
     */
    public function printAddition($policyId, $endorsementId)
    {
        $policy = Policy::findOrFail($policyId);
        $endorsement = $policy->endorsements()->findOrFail($endorsementId);

        // Optionally ensure the endorsement is of type addition (soft check)
        // if ($endorsement->type !== 'addition') { abort(400, 'Endorsement is not an addition.'); }

        return $this->downloadEndorsementPdf($policy, $endorsement, 'addition');
    }

    /**
     * Helper: generate and return the PDF download for a given endorsement.
     *
     * @param  \App\Models\Policy  $policy
     * @param  \App\Models\Endorsement  $endorsement
     * @param  string|null $label  optional label to use in filename (e.g. 'deletion')
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    private function downloadEndorsementPdf(Policy $policy, Endorsement $endorsement, ?string $label = null)
    {
        $view = 'endorsements.endorsement_note'; // reuse existing cancellation/credit-note view
        $typeLabel = $label ? $label : ($endorsement->type ?? 'endorsement');
        $fileName = sprintf('%s_endorsement_%d.pdf', $typeLabel, $endorsement->id);

        $pdf = Pdf::loadView($view, compact('policy', 'endorsement'));

        return $pdf->download($fileName);
    }

    // Replace the existing printNote method with this flexible version
    public function printNote($policyIdOrEndorsementId, $endorsementId = null)
    {
        // Called via /policies/{policy}/endorsements/{endorsement}/print
        if (!is_null($endorsementId)) {
            $policy = \App\Models\Policy::findOrFail($policyIdOrEndorsementId);
            $endorsement = $policy->endorsements()->findOrFail($endorsementId);
        } else {
            // Called via top-level /endorsements/{endorsement}/print
            $endorsement = \App\Models\Endorsement::findOrFail($policyIdOrEndorsementId);
            // Prefer relation; fallback to policy_id
            $policy = $endorsement->policy ?? \App\Models\Policy::findOrFail($endorsement->policy_id);
        }

        // Reuse existing helper to generate/download the PDF
        return $this->downloadEndorsementPdf($policy, $endorsement, $endorsement->type ?? 'endorsement');
    }
}
