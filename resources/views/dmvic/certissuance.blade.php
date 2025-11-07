{{-- resources/views/dmvic/certissuance.blade.php --}}
@extends('layouts.ui')
@section('page_title', 'Issue DMVIC Certificate')
@section('content')

<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm">
        <h2 class="font-semibold text-[#253E8B] mb-3">Issue DMVIC Certificate</h2>
        
        {{-- Show existing certificate info if present --}}
        @if(!empty($existingCertificate))
        <div class="mb-4 p-3 border rounded bg-gray-50">
            <strong>Existing Certificate:</strong>
            <div>Certificate #: <span class="font-medium">{{ $existingCertificate->certificate_no ?? 'N/A' }}</span></div>
            <div>Transaction #: <span class="font-medium">{{ $existingCertificate->transaction_no ?? 'N/A' }}</span></div>
            <div>Issued At: <span class="font-medium">{{ optional($existingCertificate->issued_at)->format('d/m/Y H:i') ?? 'N/A' }}</span></div>
            <div>Status: <span class="font-medium">{{ $existingCertificate->status ?? 'N/A' }}</span></div>
            <div class="mt-2 space-x-2">
                @if(!empty($existingCertificate->certificate_no))
                <a href="{{ route('dmvic.download', $policy->id) }}" class="inline-block px-3 py-2 rounded-md bg-[rgb(135,133,161)] text-white font-medium">Download</a>
                <a href="{{ route('dmvic.view', $policy->id) }}" class="inline-block px-3 py-2 rounded-md bg-[rgb(135,133,161)] text-white font-medium">View</a>
                @endif
                @if(isset($existingCertificate->status) && $existingCertificate->status === 'issued')
                <form method="POST" action="{{ route('dmvic.cancel', $policy->id) }}" class="inline-block" onsubmit="return confirm('Cancel this certificate?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-10 py-3 rounded-md bg-[#3730a3] text-white font-medium">Cancel Certificate</button>
                </form>
                @endif
            </div>
        </div>
        @endif

        {{-- Form --}}
        <form id="dmvicIssueForm" method="POST" action="{{ route('dmvic.certificates.issue', $policy->id) }}">
            @csrf
            
            {{-- Hidden: policy id --}}
            <input type="hidden" name="policy_id" value="{{ $policy->id }}">

            <div class="grid md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">File No</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-gray-50 cursor-not-allowed text-gray-900" value="{{ $policy->fileno ?? ($policy->file_no ?? 'N/A') }}" readonly>
                </div>
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Insured / Customer</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-gray-50 cursor-not-allowed text-gray-900" value="{{ $policy->insured ?? $policy->customer_name ?? 'N/A' }}" readonly>
                </div>
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Insurer</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-gray-50 cursor-not-allowed text-gray-900" value="{{ $policy->insurer_name ?? $policy->insurer ?? 'N/A' }}" readonly>
                </div>
            </div>

            <div class="grid md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Policy No</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-gray-50 cursor-not-allowed text-gray-900" value="{{ $policy->policy_no ?? 'N/A' }}" readonly>
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Reg. No</label>
                    <input type="text" id="registrationnumber" name="registrationnumber" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->reg_no ?? '' }}" />
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Chassis No</label>
                    <input type="text" name="chassisnumber" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->chassisno ?? '' }}">
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Engine No</label>
                    <input type="text" name="enginenumber" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->engine_no ?? '' }}">
                </div>
            </div>

            <div class="grid md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Vehicle Make</label>
                    <input type="text" name="vehiclemake" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->make ?? '' }}">
                </div>
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Vehicle Model</label>
                    <input type="text" name="vehiclemodel" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->model ?? '' }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">YOM</label>
                    <input type="number" name="yearofmanufacture" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->yom ?? $policy->year_of_registration ?? '' }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Sum Insured</label>
                    <input type="number" step="0.01" name="suminsured" id="sum_insured" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->sum_insured ?? $policy->suminsured ?? 0 }}" />
                </div>
            </div>

            <hr class="my-4">

            <h3 class="font-semibold text-[#253E8B] mb-3">DMVIC Inputs (required)</h3>

            <div class="grid md:grid-cols-12 gap-4 mb-3">
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Certificate Class <span class="text-red-600">*</span></label>
                    <select id="certificate_class" name="certificate_class" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900">
                        <option value="">Select class</option>
                        <option value="A" {{ (old('certificate_class', $autoClass ?? '') === 'A') ? 'selected' : '' }}>Class A</option>
                        <option value="B" {{ (old('certificate_class', $autoClass ?? '') === 'B') ? 'selected' : '' }}>Class B</option>
                        <option value="C" {{ (old('certificate_class', $autoClass ?? '') === 'C') ? 'selected' : '' }}>Class C</option>
                        <option value="D" {{ (old('certificate_class', $autoClass ?? '') === 'D') ? 'selected' : '' }}>Class D</option>
                    </select>
                    @if(!empty($autoClass))
                    <div class="text-xs text-gray-500 mt-1">Auto-selected from policy type: {{ $autoClass }}</div>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Type of Cover <span class="text-red-600">*</span></label>
                    <select name="typeofcover" id="Typeofcover" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900">
                        <option value="">Select</option>
                        <option value="100" {{ (string)old('typeofcover', (string)($autoCoverCode ?? '')) === '100' ? 'selected' : '' }}>100 - Comprehensive (COMP)</option>
                        <option value="200" {{ (string)old('typeofcover', (string)($autoCoverCode ?? '')) === '200' ? 'selected' : '' }}>200 - Third-party (TPO)</option>
                        <option value="300" {{ (string)old('typeofcover', (string)($autoCoverCode ?? '')) === '300' ? 'selected' : '' }}>300 - Third-party, Theft & Fire (TPTF)</option>
                    </select>
                    @if(!empty($autoCoverCode))
                    <div class="text-xs text-gray-500 mt-1">Auto-selected from policy coverage.</div>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Member Company (DMVIC MemberCompanyID) <span class="text-red-600">*</span></label>
                    @php
                    $defaultMemberId = null;
                    if (!empty($memberCompanyRows)) {
                        foreach ($memberCompanyRows as $row) {
                            if (isset($policy->insurer_id) && $row->insurer_id == $policy->insurer_id) {
                                $defaultMemberId = $row->member_company_id;
                                break;
                            }
                        }
                    }
                    $selectedMember = old('membercompanyid', $defaultMemberId);
                    @endphp

                    @if(!empty($memberLookupError) || ($memberCompanyRows ?? collect())->isEmpty())
                    <div class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-2 mb-2">
                        {{ $memberLookupError ?? 'Member companies could not be loaded. Please enter the DMVIC MemberCompanyID manually.' }}
                    </div>
                    <input type="number" name="membercompanyid" id="Membercompanyid" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $selectedMember }}" placeholder="Enter DMVIC MemberCompanyID">
                    @if(!empty($policy->insurer_name))
                    <div class="text-xs text-gray-500 mt-1">Policy insurer: {{ $policy->insurer_name }}</div>
                    @endif
                    @else
                    <select name="membercompanyid" id="Membercompanyid" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900">
                        <option value="">Select company</option>
                        @foreach(($memberCompanyRows ?? []) as $row)
                        <option value="{{ $row->member_company_id }}" {{ (string)$selectedMember === (string)$row->member_company_id ? 'selected' : '' }}>
                            {{ $row->insurer_name }} ({{ $row->member_company_id }})
                        </option>
                        @endforeach
                    </select>
                    @if($defaultMemberId)
                    <div class="text-xs text-gray-500 mt-1">Detected from policy insurer: {{ $policy->insurer_name ?? 'N/A' }} → MemberCompanyID {{ $defaultMemberId }}</div>
                    @endif
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-12 gap-4 mb-3">
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Commencing Date <span class="text-red-600">*</span></label>
                    <input type="date" name="commencing_date" id="Commencingdate_raw" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ old('commencing_date', $autoCommencing ?? (isset($policy->start_date) ? \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') : '')) }}">
                    @if(!empty($autoCommencing))
                    <div class="text-xs text-gray-500 mt-1">Defaulted to today.</div>
                    @endif
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Expiring Date <span class="text-red-600">*</span></label>
                    <input type="date" name="expiring_date" id="Expiringdate_raw" class="w-full border border-[#e92626] rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ old('expiring_date', $autoExpiring ?? (isset($policy->end_date) ? \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') : '')) }}">
                    @if(!empty($autoExpiring))
                    <div class="text-xs text-gray-500 mt-1">Defaulted to 365 days from today.</div>
                    @endif
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Phone <span class="text-red-600">*</span></label>
                    <input type="text" name="phonenumber" id="Phonenumber" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ old('phonenumber', $policy->customer_phone ?? $policy->phone ?? '') }}">
                </div>
                <div class="md:col-span-3">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Email <span class="text-red-600">*</span></label>
                    <input type="email" name="email" id="Email" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ old('email', $policy->customer_email ?? $policy->email ?? '') }}">
                </div>
            </div>

            <div id="extraForA" class="hidden">
                <div class="grid md:grid-cols-12 gap-4 mb-3">
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Licensed To Carry (A,D)</label>
                        <input type="number" name="licensedtocarry" id="Licensedtocarry" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" min="0" step="1">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Body Type</label>
                        <input type="text" name="bodytype" id="Bodytype" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->body_type ?? '' }}">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Huduma Number (optional)</label>
                        <input type="text" name="hudumanumber" id="HudumaNumber" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" maxlength="12" placeholder="123456789012">
                    </div>
                </div>
            </div>

            <div id="extraForB" class="hidden">
                <div class="grid md:grid-cols-12 gap-4 mb-3">
                    <div id="vehicle_type_wrap" class="md:col-span-4" style="display: none;">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Vehicle Type <span class="text-red-600">*</span></label>
                        <select name="vehicletype" id="vehicletype" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900">
                            <option value="">Select vehicle type</option>
                            @foreach(($vehicleTypes ?? []) as $vtKey => $vtLabel)
                                <option value="{{ $vtKey }}" {{ old('vehicletype') == $vtKey ? 'selected' : '' }}>{{ $vtLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Tonnage Carrying Capacity</label>
                        <input type="number" name="tonnagecarryingcapacity" id="TonnageCarryingCapacity" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" min="0" step="1">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Tonnage</label>
                        <input type="number" name="tonnage" id="Tonnage" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" min="0" step="1">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block font-medium mb-2 text-[#253E8B] text-sm">Licensed To Carry</label>
                        <input type="number" name="LicensedToCarryB_placeholder" id="LicensedToCarryB" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" min="0" step="1">
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Insured PIN</label>
                    <input type="text" name="insuredpin" id="InsuredPIN" maxlength="11" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ old('insuredpin', $policy->customer_kra_pin ?? $policy->kra_pin ?? '') }}">
                </div>
                <div class="md:col-span-4">
                    <label class="block font-medium mb-2 text-[#253E8B] text-sm">Year of Manufacture</label>
                    <input type="number" name="yearofmanufacture" id="Yearofmanufacture" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-2 bg-white text-gray-900" value="{{ $policy->yom ?? '' }}">
                </div>
            </div>

            <div class="flex items-center gap-x-4 mt-6">
                <button type="submit" id="issueBtn" class="px-10 py-3 rounded-md bg-[#3730a3] text-white font-medium">
                    <i class="fas fa-certificate mr-2"></i> Issue Certificate
                </button>
                <a href="{{ route('dmvic.dashboard') }}" class="inline-block px-3 py-2 rounded-md bg-[rgb(135,133,161)] text-white font-medium">Back</a>
                <div id="formMsg" class="ml-4 text-sm"></div>
            </div>
        </form>
    </div>
</div>

<script>
// Simple UI logic to toggle sections by class
(function(){
    const classSelect = document.getElementById('certificate_class');
    const subtype = document.getElementById('type_of_certificate');
    const vehicleTypeWrap = document.getElementById('vehicle_type_wrap');
    const extraA = document.getElementById('extraForA');
    const extraB = document.getElementById('extraForB');

    function toggleSections() {
        const cls = classSelect.value;
        
        if (vehicleTypeWrap) {
            vehicleTypeWrap.style.display = (cls === 'B') ? 'block' : 'none';
        }
        
        extraA.classList.toggle('hidden', !(cls === 'A' || cls === 'D'));
        extraB.classList.toggle('hidden', cls !== 'B');

        // filter subtype options depending on class
        if (subtype) {
            for (const opt of subtype.options) {
                const optClass = opt.getAttribute('data-class');
                if (!optClass) {
                    opt.style.display = classSelect.value ? 'none' : 'block';
                } else {
                    opt.style.display = (optClass === cls) ? 'block' : 'none';
                }
            }
            if (!subtype.querySelector('option[selected], option:checked')) {
                subtype.value = '';
            }
        }
    }

    classSelect.addEventListener('change', toggleSections);
    toggleSections();
})();
</script>

@endsection