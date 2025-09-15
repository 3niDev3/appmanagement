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
                <div class="apk-content">
                    <div class="apk-detail">
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
                {{-- <a href="{{ route('project.uploadForm', $project->slug) }}" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload First APK
                </a> --}}
            </div>
        @endforelse
    @endif
</div>

<style>
.apk-row { transition: transform 0.2s;, box-shadow 0.2s;}
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

.apk-detail{
    display: flex;
    align-items: center;
}


.apk-content{
    display: flex;
    justify-content: space-between;
    align-items: center;
    word-break: break-all;
}

/* Responsive Design */

@media (max-width: 992px) {
    .apk-content{
        flex-direction: column;
        gap: 10px;
        align-items: baseline;
    }

    .apk-row {
        flex-direction: column;
        gap: 1rem;
        align-items: normal;
    }

    .apk-detail{
        display: block;
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

/* Enhanced download progress styles */
.download-progress-container {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    to {
        opacity: 1;
        max-height: 100px;
        padding-top: 15px;
        padding-bottom: 15px;
    }
}

.download-progress-container .progress {
    height: 12px;
    background-color: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.download-progress-container .progress-bar {
    transition: width 0.3s ease;
    background: linear-gradient(45deg, #28a745 25%, transparent 25%, transparent 50%, #28a745 50%, #28a745 75%, transparent 75%);
    background-size: 20px 20px;
    animation: progressAnimation 1s linear infinite;
}

@keyframes progressAnimation {
    0% { background-position: 0 0; }
    100% { background-position: 20px 0; }
}

.download-progress-container .progress-bar.bg-danger {
    background: #dc3545;
    animation: none;
}

.download-status {
    font-weight: 500;
    color: #495057;
}

.download-percentage {
    font-weight: 600;
    color: #28a745;
    font-family: 'Courier New', monospace;
}

/* Enhanced floating alerts */
.floating-alert {
    animation: slideInRight 0.3s ease-out;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.floating-alert.alert-success {
    background: rgba(40, 167, 69, 0.95);
    color: white;
    border-left: 4px solid #28a745;
}

.floating-alert.alert-danger {
    background: rgba(220, 53, 69, 0.95);
    color: white;
    border-left: 4px solid #dc3545;
}

.floating-alert.alert-info {
    background: rgba(23, 162, 184, 0.95);
    color: white;
    border-left: 4px solid #17a2b8;
}

/* Enhanced button states */
.btn-download {
    position: relative;
    transition: all 0.3s ease;
}

.btn-download:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    transform: none;
}

.btn-download:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Pulse animation for active downloads */
.downloading {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

/* Mobile responsiveness for progress */
@media (max-width: 768px) {
    .download-progress-container {
        padding: 10px;
        margin-top: 8px;
    }
    
    .floating-alert {
        left: 10px;
        right: 10px;
        min-width: auto;
        max-width: none;
    }
    
    .download-status {
        font-size: 0.8rem;
    }
    
    .download-percentage {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .download-progress-container .progress {
        height: 10px;
    }
    
    .floating-alert {
        top: 10px;
        font-size: 0.9rem;
    }
}
</style>

<script>
const csrfToken = '{{ csrf_token() }}';


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


// Enhanced download functionality with progress indicator
document.querySelectorAll('.btn-download').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();

        const apkId = this.dataset.apkId;
        const fileName = this.dataset.fileName;
        const row = this.closest('.apk-row');

        // Create progress container
        const progressContainer = document.createElement('div');
        progressContainer.className = 'download-progress-container mt-2';
        progressContainer.innerHTML = `
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%" 
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted download-status">Preparing download...</small>
                <small class="text-muted download-percentage">0%</small>
            </div>
        `;

        // Insert after apk-content
        const apkContent = row.querySelector('.apk-content');
        apkContent.insertAdjacentElement('afterend', progressContainer);

        // Add Pause, Resume, Cancel buttons
        const pauseBtn = document.createElement('button');
        pauseBtn.textContent = 'Pause';
        pauseBtn.className = 'btn btn-warning btn-sm me-2';
        const resumeBtn = document.createElement('button');
        resumeBtn.textContent = 'Resume';
        resumeBtn.className = 'btn btn-info btn-sm me-2';
        resumeBtn.style.display = 'none';
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.className = 'btn btn-danger btn-sm';
        progressContainer.append(pauseBtn, resumeBtn, cancelBtn);

        // Disable main download button
        const originalHTML = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Downloading...';

        const progressBar = progressContainer.querySelector('.progress-bar');
        const statusText = progressContainer.querySelector('.download-status');
        const percentText = progressContainer.querySelector('.download-percentage');

        let xhr = null;
        let paused = false;
        let receivedBytes = 0;
        let totalBytes = 0;
        let blobParts = [];

        const updateProgress = (loaded, total, status = 'Downloading...') => {
            const percent = total ? Math.floor((loaded / total) * 100) : 0;
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            statusText.innerText = status;
            percentText.innerText = percent + '%';
        };

        const startDownload = async (rangeStart = 0) => {
            try {
                const deviceInfo = getDeviceInfo();
                const location = await getLocation();

                xhr = new XMLHttpRequest();
                xhr.open('POST', `{{ url('/apks/download') }}/${apkId}`, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                if (rangeStart > 0) xhr.setRequestHeader('Range', `bytes=${rangeStart}-`);
                xhr.responseType = 'arraybuffer';
                xhr.timeout = 300000;

                xhr.onprogress = function(e) {
                    if (e.lengthComputable) {
                        totalBytes = e.total + rangeStart;
                        receivedBytes = e.loaded + rangeStart;
                        updateProgress(receivedBytes, totalBytes);
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200 || xhr.status === 206) {
                        blobParts.push(xhr.response);

                        // Download the file
                        const blob = new Blob(blobParts, { type: 'application/vnd.android.package-archive' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = fileName;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                        updateProgress(totalBytes, totalBytes, 'Download Completed!');
                        showMessage('Download completed successfully!', 'success');

                        // Increment download count
                        const countEl = row.querySelector('.download-count span');
                        if(countEl) countEl.innerText = parseInt(countEl.innerText) + 1;

                        // Cleanup
                        pauseBtn.remove();
                        resumeBtn.remove();
                        cancelBtn.remove();
                        setTimeout(() => progressContainer.remove(), 3000);
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    } else {
                        updateProgress(receivedBytes, totalBytes, 'Download failed!');
                        showMessage('Download failed!', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                };

                xhr.onerror = xhr.ontimeout = function() {
                    updateProgress(receivedBytes, totalBytes, 'Download failed!');
                    showMessage('Download error or timeout!', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                };

                xhr.send(JSON.stringify({
                    device_name: deviceInfo.device,
                    os_version: deviceInfo.os,
                    location: location
                }));

                updateProgress(receivedBytes, totalBytes, 'Starting download...');
            } catch (err) {
                console.error(err);
                updateProgress(receivedBytes, totalBytes, 'Download failed!');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        };

        // Start initial download
        startDownload();

        pauseBtn.addEventListener('click', function() {
            if(xhr) {
                xhr.abort();
                paused = true;
                pauseBtn.style.display = 'none';
                resumeBtn.style.display = 'inline-block';
                updateProgress(receivedBytes, totalBytes, 'Paused');
            }
        });

        resumeBtn.addEventListener('click', function() {
            if(paused) {
                paused = false;
                pauseBtn.style.display = 'inline-block';
                resumeBtn.style.display = 'none';
                startDownload(receivedBytes);
            }
        });

        cancelBtn.addEventListener('click', function() {
            if(xhr) xhr.abort();
            progressContainer.remove();
            showMessage('Download canceled', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });
});


// Helper function to update progress bar
function updateDownloadProgress(container, percentage, status = '') {
    const progressBar = container.querySelector('.progress-bar');
    const percentageElement = container.querySelector('.download-percentage');
    const statusElement = container.querySelector('.download-status');
    
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    percentageElement.textContent = percentage + '%';
    
    if (status) {
        statusElement.textContent = status;
    }
}

// Helper function to update download status
function updateDownloadStatus(container, status, percentage = null, isError = false) {
    const statusElement = container.querySelector('.download-status');
    const progressBar = container.querySelector('.progress-bar');
    
    statusElement.textContent = status;
    
    if (percentage !== null) {
        updateDownloadProgress(container, percentage);
    }
    
    if (isError) {
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-danger');
        progressBar.classList.remove('progress-bar-animated');
    } else if (percentage === 100) {
        progressBar.classList.remove('progress-bar-animated');
    }
}

// Enhanced device/OS detection with more details
function getDeviceInfo() {
    const ua = navigator.userAgent;
    let device = 'Unknown', os = 'Unknown';
    
    if (/android/i.test(ua)) {
        device = 'Android Device';
        os = 'Android';
        const match = ua.match(/Android\s([0-9\.]*)/);
        if (match) os += ' ' + match[1];
    } else if (/iphone|ipad|ipod/i.test(ua)) {
        device = /ipad/i.test(ua) ? 'iPad' : 'iPhone';
        os = 'iOS';
        const match = ua.match(/OS\s([0-9_]*)/);
        if (match) os += ' ' + match[1].replace(/_/g, '.');
    } else if (/windows/i.test(ua)) {
        device = 'Windows PC';
        os = 'Windows';
        if (/Windows NT 10.0/i.test(ua)) os += ' 10';
        else if (/Windows NT 6.3/i.test(ua)) os += ' 8.1';
        else if (/Windows NT 6.2/i.test(ua)) os += ' 8';
        else if (/Windows NT 6.1/i.test(ua)) os += ' 7';
    } else if (/macintosh|mac os x/i.test(ua)) {
        device = 'Mac';
        os = 'macOS';
        const match = ua.match(/Mac OS X\s([0-9_]*)/);
        if (match) os += ' ' + match[1].replace(/_/g, '.');
    }
    
    return { device, os };
}

// Enhanced location detection with fallback
async function getLocation() {
    try {
        const res = await fetch('https://ipapi.co/json/', { timeout: 5000 });
        const data = await res.json();
        
        if (data.error) {
            throw new Error('Location service error');
        }
        
        const city = data.city || 'Unknown City';
        const region = data.region || '';
        const country = data.country_name || 'Unknown Country';
        
        return region ? `${city}, ${region}, ${country}` : `${city}, ${country}`;
    } catch (error) {
        console.warn('Location detection failed:', error);
        
        // Fallback to timezone-based location estimation
        try {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            return `Estimated location (${timezone})`;
        } catch {
            return 'Unknown Location';
        }
    }
}

// Enhanced message display with better positioning
function showMessage(message, type = 'info', duration = 5000) {
    // Remove any existing messages
    document.querySelectorAll('.floating-alert').forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show floating-alert`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: none;
        border-radius: 8px;
    `;
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'danger' ? 'bi-exclamation-triangle' : 'bi-info-circle'} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after specified duration
    setTimeout(() => {
        if(alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }
    }, duration);
    
    // Add click to dismiss
    alertDiv.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            this.remove();
        }
    });
}




</script>
@endsection