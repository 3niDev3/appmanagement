@extends('layouts.apk')

@section('title', $project->name . ' - APKs')

@section('content')
<div class="container py-5">

    @if(isset($showLogin) && $showLogin)
        <!-- Login Form -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
                            <h4 class="fw-bold mt-2">Login Required</h4>
                            <p class="text-muted mb-0">Please log in to access <strong>{{ $project->name }}</strong> APKs</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger small py-2 mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('project.loginAndList', $project->slug) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" id="email" class="form-control form-control-sm rounded-3" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label small fw-semibold">Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-sm rounded-3" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- APK List -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">{{ $project->name }} APKs</h2>
            {{-- <div>
                <a href="{{ route('project.uploadForm', $project->slug) }}" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-upload"></i> Upload APK
                </a>
            </div> --}}
        </div>

        @php
            $currentUser = Auth::guard('web')->check() ? Auth::guard('web')->user() :
                           (Auth::guard('admin')->check() ? Auth::guard('admin')->user() : null);
        @endphp

        @if($currentUser)
            <div class="alert alert-info mb-4">
                <i class="bi bi-person-check me-2"></i>
                Welcome back, <strong>{{ $currentUser->name }}</strong>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <input type="text" class="form-control" id="searchApk" placeholder="Search applications...">
        </div>

        @forelse($apks as $apk)
            <div class="apk-row p-3 mb-3 rounded shadow-sm bg-white" data-apk-id="{{ $apk->id }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-phone fs-3 me-3 text-primary"></i>
                    <div>
                        <div class="fw-semibold">{{ $apk->filename }}</div>
                        <div class="text-muted small">{{ $apk->created_at->format('d M Y h:i A') }}</div>
                        @if($apk->uploadedBy)
                            <div class="text-muted small">By: {{ $apk->uploadedBy->name }}</div>
                        @endif
                        <div class="text-muted small download-count">
                            Downloads: <span>{{ $apk->download_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($apk->description)
                        <button class="btn btn-outline-secondary btn-sm toggle-details">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    @endif
                    <button class="btn btn-success btn-sm btn-download"
                        data-apk-id="{{ $apk->id }}"
                        data-file-name="{{ $apk->filename }}">
                        <i class="bi bi-download"></i> Download
                    </button>
                    <button class="btn btn-info btn-sm btn-history" 
                        data-apk-id="{{ $apk->id }}">
                        <i class="bi bi-clock-history"></i> History
                    </button>
                </div>
                @if($apk->description)
                    <div class="apk-details mt-2 px-4 py-2 mb-2 border-top" style="display:none;">
                        <div><strong>Description:</strong> {{ $apk->description }}</div>
                    </div>
                @endif

                <div class="download-history mt-2 px-4 py-2" style="display:none;" id="history-{{ $apk->id }}">
                    <h5>Download History</h5>
                    <div class="history-placeholder">Loading download history...</div>
                </div>
            </div>
        @empty
            <div class="text-center p-5 text-muted">
                <i class="bi bi-exclamation-circle fs-1 mb-3"></i>
                <p>No APKs uploaded yet.</p>
                <a href="{{ route('project.uploadForm', $project->slug) }}" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload First APK
                </a>
            </div>
        @endforelse
    @endif
</div>

<style>
.apk-row { transition: transform 0.2s, box-shadow 0.2s; }
.apk-row:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.apk-details { font-size: 0.9rem; color: #555; }
.toggle-details { border-radius: 50%; }
.download-history { font-size: 0.85rem; color: #555; border-top: 1px dashed #ccc; margin-top: 5px; padding-top: 5px; }
.btn-download:disabled { opacity: 0.6; }
.download-progress { 
    display: none; 
    position: absolute; 
    top: 50%; 
    left: 50%; 
    transform: translate(-50%, -50%);
}

/* Card responsiveness */
.card {
    border-radius: 1rem;
}

/* APK rows */
.apk-row { 
    transition: transform 0.2s, box-shadow 0.2s; 
    border-radius: 0.75rem;
    background: #fff;
    word-break: break-word;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.apk-row:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
}
.apk-details { 
    font-size: 0.9rem; 
    color: #555; 
}
.toggle-details { 
    border-radius: 50%; 
}
.download-history { 
    font-size: 0.85rem; 
    color: #555; 
    border-top: 1px dashed #ccc; 
    margin-top: 5px; 
    padding-top: 5px; 
}
.btn-download:disabled { 
    opacity: 0.6; 
}

/* Login card */
.card-body {
    padding: 2rem;
}
.form-label {
    font-size: 0.85rem;
}

/* Alerts */
.alert {
    border-radius: 0.75rem;
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
}

/* Responsive Design */

/* For tablets and smaller (≤ 992px) */
@media (max-width: 992px) {
    .apk-row {
        flex-direction: column;
        gap: 1rem;
        align-items: normal;
    }

}

/* For mobile (≤ 768px) */
@media (max-width: 768px) {
    .card-body {
        padding: 1.25rem;
    }
    h2.fw-bold {
        font-size: 1.4rem;
    }
    .apk-row {
        padding: 1rem;
    }
    .apk-row .fs-3 {
        font-size: 1.8rem !important;
    }
    .apk-row .btn-sm {
        font-size: 0.8rem;
        padding: 0.35rem 0.6rem;
    }
    .apk-details {
        font-size: 0.8rem;
    }
}

/* Extra small mobile (≤ 480px) */
@media (max-width: 480px) {
    .card {
        margin: 0 0.5rem;
    }
    .card-body {
        padding: 1rem;
    }
    h2.fw-bold {
        font-size: 1.2rem;
    }
    .apk-row .fw-semibold {
        font-size: 0.9rem;
    }

}
</style>

<script>
const csrfToken = '{{ csrf_token() }}';

// Device/OS detection
function getDeviceInfo() {
    const ua = navigator.userAgent;
    let device = 'Unknown', os = 'Unknown';
    if (/android/i.test(ua)) device = 'Android Device', os = 'Android';
    else if (/iphone|ipad|ipod/i.test(ua)) device = 'iPhone/iPad', os = 'iOS';
    else if (/windows/i.test(ua)) device = 'Windows PC', os = 'Windows';
    else if (/macintosh|mac os x/i.test(ua)) device = 'Mac', os = 'Mac OS';
    return { device, os };
}

// IP-based location
async function getLocation() {
    try {
        const res = await fetch('https://ipapi.co/json/');
        const data = await res.json();
        return `${data.city || 'Unknown'}, ${data.region || ''}, ${data.country_name || ''}`;
    } catch {
        return 'Unknown';
    }
}

// Download APK and track
document.querySelectorAll('.btn-download').forEach(btn => {
    btn.addEventListener('click', async function(e){
        e.preventDefault();
        
        const apkId = this.dataset.apkId;
        const fileName = this.dataset.fileName;

        // Disable button and show loading
        this.disabled = true;
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="spinner-border spinner-border-sm" role="status"></i> Downloading...';

        try {
            const deviceInfo = getDeviceInfo();
            const location = await getLocation();

            // Send download request to backend
            const response = await fetch(`{{ url('/apks/download') }}/${apkId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    device_name: deviceInfo.device,
                    os_version: deviceInfo.os,
                    location: location
                })
            });

            if(!response.ok){
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            // Create blob and download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            // Update download count visually
            const row = this.closest('.apk-row');
            const countEl = row.querySelector('.download-count span');
            if(countEl) {
                countEl.innerText = parseInt(countEl.innerText) + 1;
            }

            // Show success message
            showMessage('Download started successfully!', 'success');

        } catch (error) {
            console.error('Download error:', error);
            showMessage('Error downloading file: ' + error.message, 'danger');
        } finally {
            // Re-enable button
            this.disabled = false;
            this.innerHTML = originalHTML;
        }
    });
});

// History button functionality
document.querySelectorAll('.btn-history').forEach(btn => {
    btn.addEventListener('click', function() {
        const apkId = this.dataset.apkId;
        const row = this.closest('.apk-row');
        const historyDiv = row.querySelector('.download-history');
        
        if(historyDiv.style.display === 'none' || !historyDiv.style.display) {
            fetchDownloadHistory(apkId, historyDiv);
        } else {
            historyDiv.style.display = 'none';
        }
    });
});

// Toggle details
document.querySelectorAll('.toggle-details').forEach(btn=>{
    btn.addEventListener('click', function(){
        const details = this.closest('.apk-row').querySelector('.apk-details');
        if(details){
            const icon = this.querySelector('i');
            if(details.style.display === 'none' || !details.style.display){
                details.style.display = 'block';
                icon.classList.replace('bi-chevron-down','bi-chevron-up');
            } else {
                details.style.display = 'none';
                icon.classList.replace('bi-chevron-up','bi-chevron-down');
            }
        }
    });
});

// Search functionality
const searchInput = document.getElementById('searchApk');
if(searchInput){
    searchInput.addEventListener('input', function(){
        const query = this.value.toLowerCase();
        document.querySelectorAll('.apk-row').forEach(row=>{
            const title = row.querySelector('.fw-semibold').innerText.toLowerCase();
            const descEl = row.querySelector('.apk-details');
            const description = descEl ? descEl.innerText.toLowerCase() : '';
            row.style.display = (title.includes(query) || description.includes(query)) ? '' : 'none';
        });
    });
}

// Fetch download history
async function fetchDownloadHistory(apkId, historyDiv){
    try {
        const res = await fetch(`{{ url('/apks/history') }}/${apkId}`);
        if(!res.ok) throw new Error('Failed to fetch history');
        
        const data = await res.json();

        let html = '<h5>Download History</h5>';
        if(data.length > 0){
            data.forEach(item=>{
                const date = new Date(item.created_at).toLocaleString();
                html += `<div class="border-bottom py-2">
                    <div><strong>Device:</strong> ${item.device_name || 'Unknown'}</div>
                    <div><strong>OS:</strong> ${item.os_version || 'Unknown'}</div>
                    <div><strong>Location:</strong> ${item.location || 'Unknown'}</div>
                    <div><strong>Downloaded:</strong> ${date}</div>
                </div>`;
            });
        } else {
            html += '<div class="text-muted">No download history available</div>';
        }
        historyDiv.innerHTML = html;
        historyDiv.style.display = 'block';
    } catch(error) {
        historyDiv.innerHTML = '<h5>Download History</h5><div class="text-danger">Error loading history</div>';
        historyDiv.style.display = 'block';
    }
}

// Show message function
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if(alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endsection