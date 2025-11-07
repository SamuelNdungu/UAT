@extends('layouts.ui')
@section('page_title', 'DMVIC Double Issuance Check')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div>
        
        <form id="doubleIssuanceForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehicle Registration Number -->
                <div class="form-group">
                    <label for="vehicle_registration_number" class="form-label">Vehicle Registration Number</label>
                    <input type="text" id="vehicle_registration_number" name="vehicle_registration_number" 
                           class="form-input" placeholder="e.g. KCC410H" maxlength="15">
                    <p class="text-xs text-gray-500 mt-1">Enter vehicle registration number (max 15 characters)</p>
                </div>
                
                <!-- Chassis Number -->
                <div class="form-group">
                    <label for="chassis_number" class="form-label">Chassis Number (Optional)</label>
                    <input type="text" id="chassis_number" name="chassis_number" 
                           class="form-input" placeholder="Enter chassis number" 
                           minlength="4" maxlength="20" pattern="[A-Za-z0-9]+">
                    <p class="text-xs text-gray-500 mt-1">4-20 alphanumeric characters, no special characters</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Policy Start Date -->
                <div class="form-group">
                    <label for="policy_start_date" class="form-label">Policy Start Date</label>
                    <input type="date" id="policy_start_date" name="policy_start_date" 
                           class="form-input" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <!-- Policy End Date -->
                <div class="form-group">
                    <label for="policy_end_date" class="form-label">Policy End Date</label>
                    <input type="date" id="policy_end_date" name="policy_end_date" 
                           class="form-input" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="result" class="form-label">API Response</label>
                <textarea id="result" class="form-input h-48" readonly></textarea>
            </div>
            
            <div class="flex items-center justify-end space-x-4 pt-4">
                <button type="button" onclick="window.history.back()" class="btn-cancel">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search mr-2"></i> Check Double Issuance
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    document.getElementById('policy_start_date').valueAsDate = tomorrow;   
    
    // Form submission
    document.getElementById('doubleIssuanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            policystartdate: formatDate(document.getElementById('policy_start_date').value),
            policyenddate: formatDate(document.getElementById('policy_end_date').value),
            vehicleregistrationnumber: document.getElementById('vehicle_registration_number').value,
            chassisnumber: document.getElementById('chassis_number').value
        };
        
        // Validate at least one of registration or chassis number is provided
        if (!formData.vehicleregistrationnumber && !formData.chassisnumber) {
            alert('Please provide either Vehicle Registration Number or Chassis Number');
            return;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Checking...';
        
        // Make API call
        fetch('{{ route("dmvic.double-issuance.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + '{{ config("services.dmvic.token") }}',
                'ClientID': '{{ config("services.dmvic.client_id") }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            // Format the response for better display
            let formattedResponse = '';
            
            if (data.success && data.data) {
                if (data.data.callbackObj && Array.isArray(data.data.callbackObj.DoubleInsurance) && data.data.callbackObj.DoubleInsurance.length > 0) {
                    // If we have double insurance records
                    formattedResponse = '⚠️ DOUBLE INSURANCE FOUND ⚠️\n\n';
                    data.data.callbackObj.DoubleInsurance.forEach((record, index) => {
                        formattedResponse += `Record ${index + 1}:\n`;
                        formattedResponse += `  - Certificate No: ${record.InsuranceCertificateNo}\n`;
                        formattedResponse += `  - Insurance Co: ${record.MemberCompanyName}\n`;
                        formattedResponse += `  - Cover End Date: ${record.CoverEndDate}\n`;
                        formattedResponse += `  - Registration: ${record.RegistrationNumber}\n`;
                        formattedResponse += `  - Chassis: ${record.ChassisNumber}\n\n`;
                    });
                } else if (data.data.Error && data.data.Error.length > 0) {
                    // If we have an error message
                    formattedResponse = `❌ ${data.data.Error[0].errorText || 'No records found'}\n`;
                } else {
                    formattedResponse = 'No double insurance records found.\n';
                }
                
                // Add API reference if available
                if (data.data.APIRequestNumber) {
                    formattedResponse += `\nReference: ${data.data.APIRequestNumber}`;
                }
            } else {
                // Fallback to raw response if format is unexpected
                formattedResponse = JSON.stringify(data, null, 2);
            }
            
            document.getElementById('result').value = formattedResponse;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('result').value = '❌ Error: ' + (error.message || 'An unknown error occurred');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    // Format date to DD/MM/YYYY
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
    
    // Set minimum date for end date based on start date
    document.getElementById('policy_start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('policy_end_date');
        
        // Ensure end date is not before start date
        if (new Date(endDateInput.value) < startDate) {
            const nextDay = new Date(startDate);
            nextDay.setDate(startDate.getDate() + 1);
            endDateInput.valueAsDate = nextDay;
        }
        
        // Set min attribute to start date
        endDateInput.min = this.value;
    });
});
</script>

<style>
/* Reuse existing styles from create.blade.php */
.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #253E8B;
    font-size: 14px;
}

.form-input, textarea.form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    background: #fff;
    transition: all 0.2s ease;
    color: #111827;
}

.form-input:focus, textarea.form-input:focus {
    border-color: #253E8B;
    box-shadow: 0 0 0 3px rgba(37, 62, 139, 0.1);
    outline: none;
}

.btn-primary {
    background: #3730a3;
    color: #fff;
    font-weight: 500;
    padding: 0.75rem 2.5rem;
    border-radius: 0.375rem;
    transition: background 0.2s;
    box-shadow: 0 2px 8px rgba(79,70,229,0.10);
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: #4f46e5;
}

.btn-cancel {
    background: rgb(135, 133, 161);
    color: #fff;
    font-weight: 500;
    padding: 0.75rem 2.5rem;
    border-radius: 0.375rem;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-cancel:hover {
    background: rgb(100, 98, 120);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
