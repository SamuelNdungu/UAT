@extends('layouts.appPages')


@section('content')
{{-- REMOVED .container and .row/.col WRAPPERS --}}
{{-- Using .container-fluid or a max-width container directly on the card wrapper --}}
<div class="container-fluid py-4"> 
    <div class="row justify-content-center">
        {{-- Use col-12 to remove width restriction, or remove row/col entirely --}}
        <div class="col-12"> 
            
            <div class="card shadow-lg rounded-3">
                
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top-3">
                    <h4 class="mb-0">🤖 AI Assistant</h4>
                </div>

                <div class="card-body">
                    <div id="status-placeholder"></div>

                    <form id="ai-form">
                        <div class="form-group mb-3">
                            <textarea id="prompt" name="prompt" class="form-control" placeholder="Ask the assistant a question..." rows="8" style="resize: vertical;"></textarea>
                        </div>

                                                <!-- Confirmation modal trigger (server-driven) -->
                                                <input type="hidden" id="ai-confirm-token" value="" />

                                                <!-- Confirmation Modal -->
                                                <div class="modal fade" id="aiConfirmModal" tabindex="-1" aria-labelledby="aiConfirmModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="aiConfirmModalLabel">Confirm action</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                This action will generate a statement and may send an email to the customer. Do you want to proceed?
                                                                <div id="aiConfirmPrompt" class="mt-2 text-muted small"></div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button id="aiConfirmBtn" type="button" class="btn btn-primary">Yes, proceed</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div>
                                <button id="send-btn" type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> Send
                                </button>
                                <span id="loading" class="ms-3 text-info fw-bold" style="display:none">Thinking...</span>
                            </div>
                        </div>
                    </form>

                    <h5 class="mt-4 pt-2 border-top">Assistant Reply:</h5>
                    <div id="reply" class="p-3 border rounded shadow-sm bg-white" 
                        style="min-height:120px; white-space:pre-wrap; font-family: monospace, sans-serif;">
                        <span class="text-muted fst-italic">Awaiting your question...</span>
                    </div>

                    <div id="method" class="mt-2 text-muted small text-end"></div>
                    <div id="error" class="mt-2 p-2 text-danger bg-light-danger border border-danger rounded" style="display:none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('ai-form');
    const sendBtn = document.getElementById('send-btn');
    const loading = document.getElementById('loading');
    const replyEl = document.getElementById('reply');
    const errorEl = document.getElementById('error');
    const placeholderText = '<span class="text-muted fst-italic">Awaiting your question...</span>';

    replyEl.innerHTML = placeholderText;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorEl.style.display = 'none';
        replyEl.innerText = '';

        sendBtn.disabled = true;
        loading.style.display = 'inline';

        const promptText = document.getElementById('prompt').value || '';

        try {
            // First attempt: post the prompt. Server may ask for confirmation.
            const res = await fetch('{{ route('ai.ask') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({ prompt: promptText })
            });

            const data = await res.json();

            if (!res.ok) {
                const msg = data.error || data.message || 'AI service returned an error';
                errorEl.innerText = msg;
                errorEl.style.display = 'block';
                replyEl.innerHTML = placeholderText;
                console.error('AI error', data);
                return;
            }

            // If the server requires confirmation, show the modal with token
            if (data.confirm_required) {
                document.getElementById('ai-confirm-token').value = data.confirm_token || '';
                document.getElementById('aiConfirmPrompt').innerText = promptText.substring(0, 240);
                const modalEl = document.getElementById('aiConfirmModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                // When user confirms, re-submit with token
                document.getElementById('aiConfirmBtn').onclick = async function () {
                    modal.hide();
                    // Re-post with confirm_token
                    loading.style.display = 'inline';
                    try {
                        const second = await fetch('{{ route('ai.ask') }}', {
                            method: 'POST',
                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                            body: JSON.stringify({ prompt: promptText, confirm_token: document.getElementById('ai-confirm-token').value })
                        });

                        const d2 = await second.json();
                        if (!second.ok) {
                            errorEl.innerText = d2.error || d2.message || 'AI service returned an error';
                            errorEl.style.display = 'block';
                            replyEl.innerHTML = placeholderText;
                            console.error('AI error', d2);
                        } else {
                            replyEl.innerText = d2.reply || d2.response || 'No reply received.';
                            document.getElementById('method').innerText = d2.method ? 'Delivered via: ' + d2.method.toUpperCase() : '';
                            // Bootstrap Toast
                            showBootstrapToast('Success', d2.reply || 'Action completed');
                        }
                    } catch (err) {
                        errorEl.innerText = 'Could not reach AI service. Please try again later.';
                        errorEl.style.display = 'block';
                        replyEl.innerHTML = placeholderText;
                        console.error('Fetch failed', err);
                    } finally {
                        loading.style.display = 'none';
                        sendBtn.disabled = false;
                    }
                };

                return; // wait for confirmation
            }

            // Normal (no confirmation) response
            replyEl.innerText = data.reply || data.response || 'No reply received.';
            document.getElementById('method').innerText = data.method ? 'Delivered via: ' + data.method.toUpperCase() : '';

            // Show Bootstrap toast for successes
            if (data.method === 'function' && (String(data.reply).toLowerCase().includes('sent') || String(data.reply).toLowerCase().includes('statement generated') || String(data.reply).toLowerCase().includes('attached'))) {
                showBootstrapToast('Success', data.reply);
            }

        } catch (err) {
            errorEl.innerText = 'Could not reach AI service. Please try again later.';
            errorEl.style.display = 'block';
            replyEl.innerHTML = placeholderText;
            console.error('Fetch failed', err);
        } finally {
            sendBtn.disabled = false;
            loading.style.display = 'none';
        }
    });

    // Show AI HTTP service status
    (async () => {
        try {
            const s = await fetch('{{ route('ai.status') }}');
            const j = await s.json();
            
            const statusEl = document.createElement('div');
            statusEl.className = 'mb-3 text-end';
            
            if (j.ok) {
                statusEl.innerHTML = '<span class="badge bg-success text-white p-2">✅ AI HTTP OK</span>';
            } else {
                 statusEl.innerHTML = '<span class="badge bg-warning text-dark p-2">⚠️ AI HTTP unreachable</span>';
            }
            
            document.getElementById('status-placeholder').appendChild(statusEl);

        } catch (err) {
            // ignore
        }
    })();
})();

// Simple toast helper (Bootstrap 5 style)
function showToast(title, message) {
    // create container if missing
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = 99999;
        document.body.appendChild(container);
    }

    const t = document.createElement('div');
    t.className = 'alert alert-success shadow-sm';
    t.style.minWidth = '280px';
    t.innerHTML = '<strong>' + title + '</strong><div style="font-size:0.9em;margin-top:4px">' + message + '</div>';
    container.appendChild(t);

    setTimeout(() => {
        t.style.transition = 'opacity 0.4s ease';
        t.style.opacity = 0;
        setTimeout(() => t.remove(), 500);
    }, 4000);
}

// Show confirm checkbox when user types a send-statement prompt
document.getElementById('prompt').addEventListener('input', function () {
    const v = this.value || '';
    const wrap = document.getElementById('confirm-wrap');
    if (/send\s+statement/i.test(v)) {
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
        const cb = document.getElementById('confirm-action'); if (cb) cb.checked = false;
    }
});

// Bootstrap toast helper
function showBootstrapToast(title, message) {
    // create container if missing
    let container = document.getElementById('bs-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'bs-toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = 99999;
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = 'toast';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">${title}</strong>
            <small class="text-muted">just now</small>
            <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;

    container.appendChild(toastEl);
    const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
    bsToast.show();
}
</script>

@endsection