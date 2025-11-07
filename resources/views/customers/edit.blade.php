@extends('layouts.appPages')

@section('content')

{{-- 
    ================================================================================
    1. PREMIUM CUSTOMER PROFILE STYLES (Copied from show.blade.php)
    ================================================================================
--}}
<div class="container-fluid py-4">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            --card-shadow: 0 12px 32px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .profile-hero {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .profile-identity {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            font-size: 2rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        .profile-meta h1 {
            margin: 0;
            font-weight: 800;
            font-size: 2.25rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-subtitle {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
            opacity: 0.9;
        }

        .status-badge {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .status-active { background: rgba(16, 185, 129, 0.3); }
        .status-inactive { background: rgba(107, 114, 128, 0.3); }
        .status-blacklisted { background: rgba(239, 68, 68, 0.3); }

        .profile-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-left: auto;
            position: relative;
            z-index: 2;
        }

        .action-btn {
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            /* Added for the submit button */
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin-top: 1rem;
        }

        @media (max-width: 1200px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        .info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .info-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
            color: white;
        }

        .card-title {
            margin: 0;
            font-weight: 700;
            font-size: 1.25rem;
            color: #1e293b;
        }

        .info-grid {
            display: grid;
            gap: 1.5rem;
        }

        .info-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #3b82f6;
        }

        .section-title {
            font-weight: 700;
            color: #475569;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 1rem;
            align-items: start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
            padding-top: 0.5rem; /* Align label with top of input */
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
            word-break: break-word;
        }
        
        /* Custom styles for form inputs in the new design */
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #e3342f;
        }

        /* Radio button styling for type selector */
        .radio-group {
            display: flex;
            gap: 2rem;
            padding-top: 0.5rem;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #1e293b;
        }

        /* Submit Button Styling */
        .submit-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-top: 2rem;
            transition: var(--transition);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            opacity: 0.9;
        }
        
        /* Document styles copied from show.blade.php for consistency */
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .doc-item {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: var(--transition);
            cursor: pointer;
            position: relative; /* For delete button */
        }

        .doc-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .doc-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.25rem;
            color: white;
            background: var(--info-gradient);
        }

        .doc-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doc-delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
            transition: var(--transition);
            opacity: 0; /* Hide by default */
        }
        
        .doc-item:hover .doc-delete-btn {
            opacity: 1; /* Show on hover */
        }
        
        .doc-delete-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

    </style>

    {{-- PHP LOGIC FOR STATUS & INITIALS --}}
    @php
        // Logic to determine the currently selected status (or old input) for the dropdown.
        if (is_object($customer->status) && property_exists($customer->status, 'value')) {
            $rawStatus = $customer->status->value;
        } else {
            $rawStatus = (string) $customer->status;
        }

        $rawLower = strtolower((string) $rawStatus);

        // Normalize only to Active or Inactive; default to Active
        if (in_array($rawLower, ['1', 'true', 'yes', 'active', 'activated'], true)) {
            $normalizedStatus = 'Active';
        } elseif (in_array($rawLower, ['0', 'false', 'no', 'inactive', 'deactivated'], true)) {
            $normalizedStatus = 'Inactive';
        } else {
            $normalizedStatus = 'Active'; // Default status if data is unclear
        }

        $storedStatusValue = old('status') ?? ($normalizedStatus === 'Active' ? '1' : '0');


        // Logic for Hero Section
        $name = $customer->customer_type === 'Individual' ? 
            trim(($customer->title ? $customer->title.' ' : '') . $customer->first_name . ' ' . $customer->last_name . ' ' . $customer->surname) : 
            $customer->corporate_name;
        
        $nameParts = array_filter(explode(' ', $name));
        $initials = collect($nameParts)->map(fn($p) => mb_substr($p, 0, 1))->take(2)->join('');

        $statusMap = [
            '1' => ['Active', 'status-active', 'fas fa-check-circle'],
            '0' => ['Inactive', 'status-inactive', 'fas fa-minus-circle'],
            'Blacklisted' => ['Blacklisted', 'status-blacklisted', 'fas fa-ban'],
        ];
        // Use the actual status property for display, falling back to '0' if null
        $displayStatus = (string)($customer->status ?? '0');
        [$statusText, $statusClass, $statusIcon] = $statusMap[$displayStatus] ?? ['Unknown', 'status-inactive', 'fas fa-question-circle'];
    @endphp

    {{-- ================================================================================
    2. PROFILE HERO SECTION (ADAPTED FOR EDIT)
    ================================================================================ --}}
    <div class="profile-hero">
        <div class="profile-identity">
            <div class="profile-avatar">
                <i class="fas fa-edit"></i>
            </div>
            
            <div class="profile-meta">
                <h1>Editing Customer: {{ $name ?: 'N/A' }}</h1>
                <div class="profile-subtitle">
                    <span><strong>Code:</strong> {{ $customer->customer_code }}</span>
                    <span class="status-badge {{ $statusClass }}">
                        <i class="{{ $statusIcon }}"></i> Current Status: {{ $statusText }}
                    </span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('customers.show', $customer->id) }}" class="action-btn">
                    <i class="fas fa-user"></i> View Profile
                </a>
                <a href="{{ route('customers.index') }}" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    {{-- ================================================================================
    3. MAIN FORM START
    ================================================================================ --}}
    @if ($errors->any())
        <div class="alert alert-danger info-card" style="border-left: 4px solid var(--warning-gradient); background: #fef3c7; color: #9a3412;">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Whoops! There were some problems with your input.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" id="customerForm">
        @csrf
        @method('PUT')
        
        <div class="dashboard-grid">
            
            {{-- ================================================================================
            LEFT COLUMN: Main Form Sections (General, Contact, KYC/Documents)
            ================================================================================ --}}
            <div>
                
                {{-- CARD: GENERAL & LEGAL INFORMATION --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--primary-gradient);">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h3 class="card-title">General & Legal Information</h3>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-user-tag"></i> Customer Type
                            </div>
                            <div class="info-row">
                                <div class="info-label">Type Select</div>
                                <div class="info-value">
                                    <div class="radio-group">
                                        <label for="individual">
                                            <input type="radio" id="individual" name="customer_type" value="Individual" 
                                                {{ old('customer_type', $customer->customer_type) === 'Individual' ? 'checked' : '' }} 
                                                onclick="showIndividualForm()">
                                            Individual
                                        </label>
                                        <label for="corporate">
                                            <input type="radio" id="corporate" name="customer_type" value="Corporate" 
                                                {{ old('customer_type', $customer->customer_type) === 'Corporate' ? 'checked' : '' }} 
                                                onclick="showCorporateForm()">
                                            Corporate
                                        </label>
                                    </div>
                                    @error('customer_type')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Customer Code</div>
                                <div class="info-value">
                                    <input type="text" name="customer_code" value="{{ old('customer_code', $customer->customer_code) }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-scale-balanced"></i> Legal & Tax
                            </div>
                            <div class="info-row">
                                <div class="info-label">KRA PIN</div>
                                <div class="info-value">
                                    <input type="text" name="kra_pin" value="{{ old('kra_pin', $customer->kra_pin) }}" class="form-control @error('kra_pin') is-invalid @enderror">
                                    @error('kra_pin')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: INDIVIDUAL DETAILS FORM --}}
                <div id="individual-form" class="info-card" style="display: none;">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="card-title">Personal Details</h3>
                    </div>
                    
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-user-edit"></i> Basic Information
                        </div>
                        <div class="info-row">
                            <div class="info-label">Title</div>
                            <div class="info-value">
                                <input type="text" name="title" value="{{ old('title', $customer->title) }}" class="form-control @error('title') is-invalid @enderror">
                                @error('title')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">First Name *</div>
                            <div class="info-value">
                                <input type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}" class="form-control @error('first_name') is-invalid @enderror">
                                @error('first_name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Last Name *</div>
                            <div class="info-value">
                                <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}" class="form-control @error('last_name') is-invalid @enderror">
                                @error('last_name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Other Name (Surname)</div>
                            <div class="info-value">
                                <input type="text" name="surname" value="{{ old('surname', $customer->surname) }}" class="form-control @error('surname') is-invalid @enderror">
                                @error('surname')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ID Number</div>
                            <div class="info-value">
                                <input type="text" name="id_number" value="{{ old('id_number', $customer->id_number) }}" class="form-control @error('id_number') is-invalid @enderror">
                                @error('id_number')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">
                                <input type="date" name="dob" value="{{ old('dob', $customer->dob) }}" class="form-control @error('dob') is-invalid @enderror">
                                @error('dob')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Occupation</div>
                            <div class="info-value">
                                <input type="text" name="occupation" value="{{ old('occupation', $customer->occupation) }}" class="form-control @error('occupation') is-invalid @enderror">
                                @error('occupation')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: CORPORATE DETAILS FORM --}}
                <div id="corporate-form" class="info-card" style="display: none;">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="card-title">Business Details</h3>
                    </div>

                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-briefcase"></i> Corporate Information
                        </div>
                        <div class="info-row">
                            <div class="info-label">Company Name *</div>
                            <div class="info-value">
                                <input type="text" name="corporate_name" value="{{ old('corporate_name', $customer->corporate_name) }}" class="form-control @error('corporate_name') is-invalid @enderror">
                                @error('corporate_name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Company No.</div>
                            <div class="info-value">
                                <input type="text" name="business_no" value="{{ old('business_no', $customer->business_no) }}" class="form-control @error('business_no') is-invalid @enderror">
                                @error('business_no')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Contact Person *</div>
                            <div class="info-value">
                                <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}" class="form-control @error('contact_person') is-invalid @enderror">
                                @error('contact_person')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Industry Class</div>
                            <div class="info-value">
                                <input type="text" name="industry_class" value="{{ old('industry_class', $customer->industry_class) }}" class="form-control @error('industry_class') is-invalid @enderror">
                                @error('industry_class')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Industry Segment</div>
                            <div class="info-value">
                                <input type="text" name="industry_segment" value="{{ old('industry_segment', $customer->industry_segment) }}" class="form-control @error('industry_segment') is-invalid @enderror">
                                @error('industry_segment')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: CONTACT & LOCATION --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--warning-gradient);">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <h3 class="card-title">Contact & Location</h3>
                    </div>

                    <div class="info-grid">
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-address-book"></i> Contact Details
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email Address</div>
                                <div class="info-value">
                                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control @error('email') is-invalid @enderror">
                                    @error('email')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone Number *</div>
                                <div class="info-value">
                                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-map"></i> Physical Address
                            </div>
                            <div class="info-row">
                                <div class="info-label">Street Address</div>
                                <div class="info-value">
                                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-control @error('address') is-invalid @enderror">
                                    @error('address')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">City</div>
                                <div class="info-value">
                                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control @error('city') is-invalid @enderror">
                                    @error('city')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Postal Code</div>
                                <div class="info-value">
                                    <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="form-control @error('postal_code') is-invalid @enderror">
                                    @error('postal_code')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">County</div>
                                <div class="info-value">
                                    <input type="text" name="county" value="{{ old('county', $customer->county) }}" class="form-control @error('county') is-invalid @enderror">
                                    @error('county')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Country</div>
                                <div class="info-value">
                                    <input type="text" name="country" value="{{ old('country', $customer->country) }}" class="form-control @error('country') is-invalid @enderror">
                                    @error('country')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: AGENT ASSIGNMENT (NEW CARD FOR AGENT SELECTION) --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--info-gradient);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="card-title">Agent Assignment</h3>
                    </div>

                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-user-tie"></i> Agent
                        </div>
                        <div class="info-row">
                            <div class="info-label">Agent</div>
                            <div class="info-value">
                                <select name="agent_id" class="form-control @error('agent_id') is-invalid @enderror">
                                    <option value="">-- Select Agent --</option>
                                    @foreach(\App\Models\Agent::all() as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id', $customer->agent_id) == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }} ({{ $agent->agent_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: KYC & DOCUMENTS (This is now the LAST card in Column 1) --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--info-gradient);">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="card-title">KYC & Documents</h3>
                    </div>

                    <div class="p-3">
                        <h4 class="mb-3"><i class="fas fa-upload me-2"></i> Upload New Documents</h4>
                        <hr class="mt-2 mb-3">

                        <div class="form-section">
                           
                            <div class="mb-3 small text-muted">Add multiple documents with descriptions for better organization.</div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="documentsTable">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th>Document Description</th>
                                            <th>Document File</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(old('document_description'))
                                            @foreach(old('document_description') as $key => $description)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>
                                                        <input type="text" name="document_description[]" class="form-control" 
                                                            placeholder="Enter document description" 
                                                            value="{{ $description }}">
                                                        @error('document_description.'.$key) 
                                                            <div class="text-danger small mt-1">{{ $message }}</div> 
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="file" name="upload_file[]" class="form-control">
                                                        @error('upload_file.'.$key) 
                                                            <div class="text-danger small mt-1">{{ $message }}</div> 
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Initial empty row --}}
                                            <tr>
                                                <td>1</td>
                                                <td>
                                                    <input type="text" name="document_description[]" class="form-control" 
                                                        placeholder="Enter document description">
                                                </td>
                                                <td>
                                                    <input type="file" name="upload_file[]" class="form-control">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <button type="button" class="btn btn-secondary" onclick="addDocumentRow()">
                                <i class="fas fa-plus"></i> Add Another Document
                            </button>
                        </div>

                        <h4 class="mt-5 mb-3"><i class="fas fa-folder-open me-2"></i> Existing Documents ({{ $documents->count() }})</h4>
                        <hr class="mt-2 mb-3">

                       {{-- EXISTING DOCUMENTS SECTION (Cleaned up logic) --}}
                        @if($documents && $documents->count() > 0)
                            <div class="documents-grid">
                                @foreach($documents as $document)
                                    <div class="doc-item" style="position: relative; cursor: pointer;">
                                        <div onclick="window.open('{{ route('documents.download', $document->id) }}', '_blank')">
                                            <div class="doc-icon">
                                                @php
                                                    // Smart icon based on file type
                                                    $extension = strtolower(pathinfo($document->original_name ?? '', PATHINFO_EXTENSION));
                                                    $icon = 'fa-file';
                                                    if (in_array($extension, ['pdf'])) $icon = 'fa-file-pdf';
                                                    elseif (in_array($extension, ['doc', 'docx'])) $icon = 'fa-file-word';
                                                    elseif (in_array($extension, ['xls', 'xlsx'])) $icon = 'fa-file-excel';
                                                    elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'fa-file-image';
                                                    elseif (in_array($extension, ['zip', 'rar'])) $icon = 'fa-file-archive';
                                                @endphp
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <div class="doc-name" title="{{ $document->description ?? $document->original_name ?? 'Document' }}">
                                                {{ $document->description ?? $document->original_name ?? 'Untitled Document' }}
                                            </div>
                                        </div>
                                        
                                        {{-- DELETE button with proper form --}}
                                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" style="position: absolute; top: 5px; right: 5px; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="doc-delete-btn" title="Delete Document" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this document?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state p-4 text-center text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">No documents have been uploaded for this customer yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
                {{-- END CARD: KYC & DOCUMENTS --}}

                {{-- SUBMIT BUTTON (Main Action) --}}
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Update
                </button>

            </div>
            
            {{-- ================================================================================
            RIGHT COLUMN: Status, Notes, Audit Timeline
            ================================================================================ --}}
            <div>
                
                {{-- CARD: STATUS CONTROL --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-circle-check"></i>
                        </div>
                        <h3 class="card-title">Status Control</h3>
                    </div>
                    
                    <div class="info-section" style="border-left: 4px solid #10b981;">
                        <div class="section-title">
                            <i class="fas fa-power-off"></i> Customer Status
                        </div>
                        <div class="info-row" style="grid-template-columns: 1fr;">
                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="1" {{ $storedStatusValue === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $storedStatusValue === '0' ? 'selected' : '' }}>Inactive</option>
                                <option value="Blacklisted" {{ $storedStatusValue === 'Blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                {{-- CARD: CUSTOMER NOTES (Extracted and cleaned up) --}}
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: var(--info-gradient);">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <h3 class="card-title">Customer Notes</h3>
                    </div>
                    
                  <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-comment-dots"></i> Private Notes
                        </div>
                        {{-- Notes is a textarea, so we don't use info-row structure --}}
                        <div class="info-value p-0 pt-3">
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>
                </div>
                
                {{-- CARD: AUDIT TIMELINE (Separated for clarity) --}}
                <div class="info-card timeline-card">
                    <div class="card-header">
                        <div class="card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="card-title">Audit Timeline</h3>
                    </div>
                    
                    <div class="timeline-item info-row" style="grid-template-columns: 1fr 1fr; border-bottom: 1px solid #e2e8f0;">
                        <span class="info-label">Profile Created</span>
                        <span class="info-value">{{ $customer->created_at?->format('M d, Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="timeline-item info-row" style="grid-template-columns: 1fr 1fr;">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value">{{ $customer->updated_at?->format('M d, Y H:i') ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ================================================================================
4. JAVASCRIPT LOGIC
================================================================================ --}}
<script>
    function showIndividualForm() {
        document.getElementById('individual-form').style.display = 'block';
        document.getElementById('corporate-form').style.display = 'none';
    }

    function showCorporateForm() {
        document.getElementById('individual-form').style.display = 'none';
        document.getElementById('corporate-form').style.display = 'block';
    }

    window.onload = function() {
        const individualChecked = document.getElementById('individual').checked;
        const corporateChecked = document.getElementById('corporate').checked;

        if (individualChecked) {
            showIndividualForm();
        } else if (corporateChecked) {
            showCorporateForm();
        } else {
            // Default to Individual if somehow none is checked (shouldn't happen on edit)
            showIndividualForm();
        }
        
        // Add smooth animations to cards after load
        const cards = document.querySelectorAll('.info-card, .timeline-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    
// Document Management Functions for Customers
function addDocumentRow() {
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;
    
    const newRow = table.insertRow();
    newRow.innerHTML = `
        <td>${rowCount + 1}</td>
        <td>
            <input type="text" name="document_description[]" class="form-control" placeholder="Enter document description">
        </td>
        <td>
            <input type="file" name="upload_file[]" class="form-control">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    updateRowNumbers();
}

function removeDocumentRow(button) {
    const row = button.closest('tr');
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    
    if (table.rows.length > 1) {
        row.remove();
        updateRowNumbers();
    } else {
        // If it's the last row, clear the inputs instead of removing the row
        const inputs = row.querySelectorAll('input[type="text"], input[type="file"]');
        inputs.forEach(input => {
            if (input.type === 'file') {
                input.value = ''; // Clear file input
            } else {
                input.value = ''; // Clear text input
            }
        });
    }
}

function updateRowNumbers() {
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    const rows = table.rows;
    
    for (let i = 0; i < rows.length; i++) {
        rows[i].cells[0].textContent = i + 1;
    }
}

// Initialize form display on page load
document.addEventListener('DOMContentLoaded', function() {
    const individualChecked = document.querySelector('input[name="customer_type"][value="Individual"]').checked;
    const corporateChecked = document.querySelector('input[name="customer_type"][value="Corporate"]').checked;

    if (individualChecked) {
        showIndividualForm();
    } else if (corporateChecked) {
        showCorporateForm();
    }

    // Add smooth animations
    const cards = document.querySelectorAll('.info-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
 
@endsection