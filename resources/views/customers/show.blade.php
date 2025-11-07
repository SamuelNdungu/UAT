@extends('layouts.appPages')

@section('content')
<div class="container-fluid py-4">
    {{-- Premium Customer Profile Styles --}}
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
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
            word-break: break-word;
        }

        .contact-action {
            margin-left: 0.5rem;
            opacity: 0.7;
            transition: var(--transition);
        }

        .contact-action:hover {
            opacity: 1;
            transform: scale(1.1);
        }

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
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
            transition: var(--transition);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .doc-item:hover .doc-delete-btn {
            opacity: 1;
        }
        
        .doc-delete-btn:hover {
            background: rgba(220, 38, 38, 1);
            transform: scale(1.1);
        }

        .timeline-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .timeline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-label {
            font-weight: 600;
            color: #64748b;
        }

        .timeline-value {
            font-weight: 700;
            color: #1e293b;
        }

        .notes-content {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #8b5cf6;
            font-style: italic;
            color: #475569;
            line-height: 1.6;
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

    {{-- PROFILE HERO SECTION --}}
    <div class="profile-hero">
        <div class="profile-identity">
            @php
                $name = $customer->customer_type === 'Individual' ? 
                    trim(($customer->title ? $customer->title.' ' : '') . $customer->first_name . ' ' . $customer->last_name . ' ' . $customer->surname) : 
                    $customer->corporate_name;
                
                $nameParts = array_filter(explode(' ', $name));
                $initials = collect($nameParts)->map(fn($p) => mb_substr($p, 0, 1))->take(2)->join('');
            @endphp
            
            <div class="profile-avatar">
                {{ strtoupper($initials ?: 'C') }}
            </div>
            
            <div class="profile-meta">
                <h1>{{ $name ?: 'N/A' }}</h1>
                <div class="profile-subtitle">
                    <span><strong>Type:</strong> {{ $customer->customer_type }}</span>
                    <span><strong>Code:</strong> {{ $customer->customer_code }}</span>
                    @php
                        $statusMap = [
                            '1' => ['Active', 'status-active', 'fas fa-check-circle'],
                            '0' => ['Inactive', 'status-inactive', 'fas fa-minus-circle'],
                            'Blacklisted' => ['Blacklisted', 'status-blacklisted', 'fas fa-ban'],
                        ];
                        $currentStatus = $customer->status;
                        if (!isset($statusMap[$currentStatus])) {
                            $currentStatus = strtolower((string)$currentStatus) === 'active' ? '1' : $currentStatus;
                            $currentStatus = strtolower((string)$currentStatus) === 'inactive' ? '0' : $currentStatus;
                        }
                        [$statusText, $statusClass, $statusIcon] = $statusMap[$currentStatus] ?? ['Unknown', 'status-inactive', 'fas fa-question-circle'];
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        <i class="{{ $statusIcon }}"></i> {{ $statusText }}
                    </span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('customers.index') }}" class="action-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                
                @if($customer && $customer->id)
                    <a href="{{ route('customers.edit', $customer->id) }}" class="action-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    
                    @if(\Illuminate\Support\Facades\Route::has('customers.print'))
                        <a href="{{ route('customers.print', $customer->id) }}" class="action-btn" target="_blank">
                            <i class="fas fa-print"></i> Print
                        </a>
                    @endif
                @endif
                
                <button class="action-btn" id="copyCodeBtn" data-code="{{ $customer->customer_code }}" title="Copy Customer Code">
                    <i class="fas fa-copy"></i> Copy Code
                </button>
            </div>
        </div>
    </div>

    {{-- MAIN DASHBOARD GRID --}}
    <div class="dashboard-grid">
        {{-- LEFT COLUMN: Comprehensive Information --}}
        <div>
            {{-- IDENTITY & LEGAL INFORMATION --}}
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <h3 class="card-title">Identity & Legal Information</h3>
                </div>
                
                <div class="info-grid">
                    @if($customer->customer_type === 'Individual')
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i> Personal Details
                        </div>
                        <div class="info-row">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ trim(($customer->title ? $customer->title.' ' : '') . $customer->first_name . ' ' . $customer->last_name . ' ' . $customer->surname) ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ID Number</div>
                            <div class="info-value">{{ $customer->id_number ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">{{ $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('M d, Y') : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Occupation</div>
                            <div class="info-value">{{ $customer->occupation ?: '-' }}</div>
                        </div>
                    </div>
                    @else
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-building"></i> Business Details
                        </div>
                        <div class="info-row">
                            <div class="info-label">Company Name</div>
                            <div class="info-value">{{ $customer->corporate_name ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Registration No</div>
                            <div class="info-value">{{ $customer->business_no ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Contact Person</div>
                            <div class="info-value">{{ $customer->contact_person ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Industry Class</div>
                            <div class="info-value">{{ $customer->industry_class ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Industry Segment</div>
                            <div class="info-value">{{ $customer->industry_segment ?: '-' }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-scale-balanced"></i> Legal & Tax Information
                        </div>
                        <div class="info-row">
                            <div class="info-label">KRA PIN</div>
                            <div class="info-value" style="font-family: 'Courier New', monospace; font-weight: 700;">{{ $customer->kra_pin ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTACT & LOCATION --}}
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon" style="background: var(--success-gradient);">
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
                                {{ $customer->email ?: '-' }}
                                @if($customer->email)
                                <a href="mailto:{{ $customer->email }}" class="contact-action" title="Send Email">
                                    <i class="fas fa-paper-plane text-primary"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">
                                {{ $customer->phone ?: '-' }}
                                @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}" class="contact-action" title="Make Call">
                                    <i class="fas fa-phone text-success"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-map"></i> Physical Address
                        </div>
                        <div class="info-row">
                            <div class="info-label">Street Address</div>
                            <div class="info-value">{{ $customer->address ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">City & Postal Code</div>
                            <div class="info-value">{{ ($customer->city ?: '-') . ' / ' . ($customer->postal_code ?: '-') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">County & Country</div>
                            <div class="info-value">{{ ($customer->county ?: '-') . ' / ' . ($customer->country ?: '-') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AGENT CARD --}}
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon" style="background: linear-gradient(135deg, #22223b 0%, #4a4e69 100%);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="card-title">Agent Information</h3>
                </div>
                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-user-tie"></i> Agent Details
                    </div>
                    <div class="info-row">
                        <div class="info-label">Agent</div>
                        <div class="info-value">
                            @if($customer->agent)
                                {{ $customer->agent->name }} ({{ $customer->agent->agent_code }})
                            @else
                                <span class="text-muted">No agent assigned</span>
                            @endif
                        </div>
                    </div>
                    @if($customer->agent)
                    <div class="info-row">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $customer->agent->phone }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $customer->agent->email }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Commission Rate</div>
                        <div class="info-value">{{ $customer->agent->commission_rate }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ $customer->agent->status }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- CUSTOMER NOTES --}}
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon" style="background: var(--warning-gradient);">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <h3 class="card-title">Additional Information</h3>
                </div>
                
                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-comment-dots"></i> Customer Notes
                    </div>
                    <div class="notes-content">
                        {{ $customer->notes ?: 'No additional notes provided for this customer.' }}
                    </div>
                </div>
            </div>
        </div>

       {{-- DOCUMENTS - THIS WILL NOW WORK! --}}
<div class="info-card">
    <div class="card-header">
        <div class="card-icon" style="background: var(--info-gradient);">
            <i class="fas fa-folder-open"></i>
        </div>
        {{-- This is the correct line, using the $documents variable --}}
        <h3 class="card-title">KYC Documents ({{ $documents->count() }})</h3>
    </div> {{-- This div now correctly closes .card-header --}}
    
    @if($documents->count() > 0)
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
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h4>No Documents</h4>
            <p>No documents have been uploaded for this customer.</p>
        </div>
    @endif
</div>
            {{-- AUDIT TIMELINE --}}
            <div class="timeline-card">
                <div class="card-header">
                    <div class="card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="card-title">Audit Timeline</h3>
                </div>
                
                <div class="timeline-item">
                    <span class="timeline-label">Profile Created</span>
                    <span class="timeline-value">{{ $customer->created_at?->format('M d, Y H:i') ?? '-' }}</span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Last Updated</span>
                    <span class="timeline-value">{{ $customer->updated_at?->format('M d, Y H:i') ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('copyCodeBtn')?.addEventListener('click', function() {
        const code = this.dataset.code || '';
        if (!code) return;
        
        navigator.clipboard?.writeText(code).then(() => {
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Copied!';
            this.style.background = 'rgba(16, 185, 129, 0.3)';
            
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.style.background = '';
            }, 2000);
        }).catch(() => {
            alert('Unable to copy customer code. Please copy manually.');
        });
    });

    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
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