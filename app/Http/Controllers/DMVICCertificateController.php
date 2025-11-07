<?php

namespace App\Http\Controllers;

use App\Models\DMVICCertificate;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DMVICCertificateController extends Controller
{
    /**
     * Display the specified certificate.
     */
    public function show(DMVICCertificate $certificate)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $certificate->load('policy')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch certificate details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Issue a new DMVIC certificate for the policy.
     */
    public function issue(Policy $policy)
    {
        try {
            // Check if certificate already exists
            if ($policy->dmvicCertificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'A DMVIC certificate already exists for this policy'
                ], 400);
            }

            // Generate certificate data
            $certificateData = [
                'policy_id' => $policy->id,
                'user_id' => auth()->id(),
                'transaction_no' => 'DMV-' . now()->format('YmdHis') . '-' . Str::random(6),
                'certificate_number' => 'DMV-' . strtoupper(Str::random(3)) . now()->format('Ymd') . str_pad($policy->id, 6, '0', STR_PAD_LEFT),
                'api_request_number' => 'REQ-' . now()->format('YmdHis') . '-' . Str::random(8),
                'member_company_id' => config('services.dmvic.client_id'),
                'type_of_cover' => $this->mapCoverType($policy->policy_type),
                'policy_holder' => $policy->customer_name,
                'commencing_date' => $policy->start_date,
                'expiring_date' => $policy->end_date,
                'registration_number' => $policy->vehicle_registration,
                'chassis_number' => $policy->chassisno,
                'phone_number' => $policy->customer->phone ?? null,
                'body_type' => $policy->body_type,
                'vehicle_make' => $policy->vehicle_make,
                'vehicle_model' => $policy->vehicle_model,
                'engine_number' => $policy->engine_no,
                'email' => $policy->customer->email ?? null,
                'sum_insured' => $policy->sum_insured,
                'insured_pin' => $policy->customer->pin ?? null,
                'year_of_manufacture' => $policy->year_of_manufacture,
                'huduma_number' => $policy->huduma_number ?? null,
            ];

            // Create the certificate
            $certificate = DMVICCertificate::create($certificateData);

            // Here you would typically make an API call to DMVIC to issue the actual certificate
            // For now, we'll just return the created certificate
            
            return response()->json([
                'success' => true,
                'message' => 'DMVIC certificate issued successfully',
                'data' => $certificate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to issue DMVIC certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified certificate.
     */
    public function cancel(DMVICCertificate $certificate)
    {
        try {
            // Here you would typically make an API call to DMVIC to cancel the certificate
            // For now, we'll just delete the local record
            
            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'DMVIC certificate cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel DMVIC certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the certificate PDF.
     */
    public function download(DMVICCertificate $certificate)
    {
        try {
            // Here you would typically generate or fetch the PDF from DMVIC
            // For now, we'll just return a JSON response
            
            return response()->json([
                'success' => true,
                'message' => 'Certificate download would be implemented here',
                'certificate' => $certificate->certificate_number
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map policy type to DMVIC cover type.
     */
    private function mapCoverType($policyType)
    {
        $policyType = strtolower($policyType);
        
        if (str_contains($policyType, 'comprehensive')) {
            return 100; // Comprehensive (COMP)
        } elseif (str_contains($policyType, 'third party') && str_contains($policyType, 'fire')) {
            return 300; // Third-party, Theft & Fire (TPTF)
        } else {
            return 200; // Default to Third-party (TPO)
        }
    }
}
