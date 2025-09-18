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
.apk-row { transition: transform 0.2s, box-shadow 0.2s;}
.apk-row:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.apk-details { font-size: 0.9rem; color: #555; }
.toggle-details { border-radius: 50%; }
.download-history { font-size: 0.85rem; color: #555; border-top: 1px dashed #ccc; margin-top: 5px; padding-top: 5px; }
.btn-download:disabled { opacity: 0.6; }

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

.bg-primary{
    background: black !important;
    color: white !important;
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

.completed {
    background: #28a745 !important;
    animation: none !important;
}

.error {
    background: #dc3545 !important;
    animation: none !important;
}

.paused {
    animation-play-state: paused !important;
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

// Download state management
class DownloadManager {
    constructor() {
        this.activeDownloads = new Map();
    }

    createDownload(apkId, fileName, downloadBtn, row) {
        const download = {
            id: apkId,
            fileName: fileName,
            button: downloadBtn,
            row: row,
            xhr: null,
            isPaused: false,
            isCancelled: false,
            isCompleted: false,
            receivedBytes: 0,
            totalBytes: 0,
            progressContainer: null,
            startTime: Date.now(),
            deviceInfo: null,
            location: null
        };

        this.activeDownloads.set(apkId, download);
        return download;
    }

    getDownload(apkId) {
        return this.activeDownloads.get(apkId);
    }

    removeDownload(apkId) {
        const download = this.activeDownloads.get(apkId);
        if (download && download.xhr) {
            download.xhr.abort();
        }
        this.activeDownloads.delete(apkId);
    }

    isDownloading(apkId) {
        const download = this.activeDownloads.get(apkId);
        return download && !download.isCompleted && !download.isCancelled;
    }
}

const downloadManager = new DownloadManager();

// Enhanced download functionality
document.querySelectorAll('.btn-download').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();

        const apkId = this.dataset.apkId;
        const fileName = this.dataset.fileName;
        const row = this.closest('.apk-row');

        // Check if already downloading
        if (downloadManager.isDownloading(apkId)) {
            showMessage('Download already in progress', 'warning');
            return;
        }

        // Create download instance
        const download = downloadManager.createDownload(apkId, fileName, this, row);
        
        // Get device info and location upfront
        download.deviceInfo = getDeviceInfo();
        download.location = getLocation();
        
        // Create and show progress container
        createProgressContainer(download);
        
        // Start download
        await startDownload(download);
    });
});

function createProgressContainer(download) {
    const progressContainer = document.createElement('div');
    progressContainer.className = 'download-progress-container';
    progressContainer.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-dark">Downloading ${download.fileName}</span>
            <span class="badge bg-primary download-percentage">0%</span>
        </div>
        <div class="progress mb-2">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%" 
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <small class="text-muted download-status">Preparing download...</small>
            <small class="text-muted download-speed">0 KB/s</small>
        </div>
        <div class="download-controls">
            <button class="btn btn-warning btn-sm btn-pause">
                <i class="bi bi-pause-fill"></i> Pause
            </button>
            <button class="btn btn-danger btn-sm btn-cancel">
                <i class="bi bi-x-circle-fill"></i> Cancel
            </button>
        </div>
    `;

    download.progressContainer = progressContainer;
    const apkContent = download.row.querySelector('.apk-content');
    apkContent.insertAdjacentElement('afterend', progressContainer);

    // Setup control event listeners
    setupDownloadControls(download);

    // Disable main download button
    download.button.disabled = true;
    download.button.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Downloading...';
}

function setupDownloadControls(download) {
    const pauseBtn = download.progressContainer.querySelector('.btn-pause');
    const cancelBtn = download.progressContainer.querySelector('.btn-cancel');

    pauseBtn.addEventListener('click', () => togglePause(download));
    cancelBtn.addEventListener('click', () => cancelDownload(download));
}

async function startDownload(download, rangeStart = 0) {
    if (download.isCancelled) return;

    try {
        const xhr = new XMLHttpRequest();
        download.xhr = xhr;

        // Only request the file, don't send tracking data yet
        xhr.open('POST', `{{ url('/apks/download') }}/${download.id}`, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        if (rangeStart > 0) {
            xhr.setRequestHeader('Range', `bytes=${rangeStart}-`);
        }
        xhr.responseType = 'arraybuffer';
        xhr.timeout = 300000;

        let lastLoaded = rangeStart;
        let lastTime = Date.now();

        xhr.onprogress = function(e) {
            if (e.lengthComputable && !download.isPaused) {
                download.totalBytes = e.total + rangeStart;
                download.receivedBytes = e.loaded + rangeStart;
                
                // Calculate speed
                const currentTime = Date.now();
                const timeDiff = (currentTime - lastTime) / 1000;
                if (timeDiff >= 1) { // Update speed every second
                    const bytesDiff = e.loaded - (lastLoaded - rangeStart);
                    const speed = timeDiff > 0 ? bytesDiff / timeDiff : 0;
                    updateProgress(download, download.receivedBytes, download.totalBytes, formatSpeed(speed));
                    lastLoaded = e.loaded + rangeStart;
                    lastTime = currentTime;
                }
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200 || xhr.status === 206) {
                if (!download.isCancelled && !download.isPaused) {
                    // Only now proceed to completion - tracking will happen here
                    completeDownload(download, xhr.response);
                }
            } else {
                failDownload(download, 'Server error: ' + xhr.status);
            }
        };

        xhr.onerror = () => failDownload(download, 'Network error occurred');
        xhr.ontimeout = () => failDownload(download, 'Download timeout');

        // Send empty body since we're not tracking start anymore
        xhr.send(JSON.stringify({}));

        updateDownloadStatus(download, 'Starting download...');

    } catch (error) {
        failDownload(download, 'Failed to start download: ' + error.message);
    }
}

function togglePause(download) {
    const pauseBtn = download.progressContainer.querySelector('.btn-pause');
    const progressBar = download.progressContainer.querySelector('.progress-bar');

    if (!download.isPaused) {
        // Pause download
        if (download.xhr) {
            download.xhr.abort();
        }
        download.isPaused = true;
        pauseBtn.innerHTML = '<i class="bi bi-play-fill"></i> Resume';
        progressBar.classList.add('paused');
        updateDownloadStatus(download, 'Paused');
        showMessage('Download paused - no tracking recorded', 'info');
    } else {
        // Resume download
        download.isPaused = false;
        pauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i> Pause';
        progressBar.classList.remove('paused');
        updateDownloadStatus(download, 'Resuming...');
        startDownload(download, download.receivedBytes);
        showMessage('Download resumed', 'info');
    }
}

function cancelDownload(download) {
    download.isCancelled = true;
    
    if (download.xhr) {
        download.xhr.abort();
    }

    // Clean up UI
    if (download.progressContainer) {
        download.progressContainer.remove();
    }
    
    // Reset download button
    download.button.disabled = false;
    download.button.innerHTML = '<i class="bi bi-download"></i> Download';
    
    // Remove from manager
    downloadManager.removeDownload(download.id);
    
    showMessage('Download cancelled - no tracking recorded', 'info');
}

async function completeDownload(download, responseData) {
    download.isCompleted = true;
    
    try {
        // Create and download blob first
        const blob = new Blob([responseData], { 
            type: 'application/vnd.android.package-archive' 
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = download.fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        // Update UI
        const progressBar = download.progressContainer.querySelector('.progress-bar');
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('completed');
        updateProgress(download, download.totalBytes || blob.size, download.totalBytes || blob.size);
        updateDownloadStatus(download, 'Download completed! Tracking...');

        // NOW track the successful download completion
        try {
            const trackingResponse = await fetch(`{{ url('/apks') }}/${download.id}/download-complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    device_name: download.deviceInfo.device,
                    os_version: download.deviceInfo.os,
                    location: download.location
                })
            });

            if (trackingResponse.ok) {
                const trackingData = await trackingResponse.json();
                
                // Update download count in UI with server-confirmed count
                if (trackingData.new_count) {
                    const countEl = download.row.querySelector('.download-count span');
                    if (countEl) {
                        countEl.textContent = trackingData.new_count;
                    }
                }
                
                updateDownloadStatus(download, 'Download completed and tracked!');
                showMessage('Download completed successfully!', 'success');
            } else {
                // File was downloaded but tracking failed - that's okay
                updateDownloadStatus(download, 'Download completed!');
                showMessage('Download completed (tracking failed)', 'warning');
            }
        } catch (trackingError) {
            console.warn('Download tracking failed:', trackingError);
            updateDownloadStatus(download, 'Download completed!');
            showMessage('Download completed (tracking unavailable)', 'warning');
        }

        // Clean up after delay
        setTimeout(() => {
            if (download.progressContainer) {
                download.progressContainer.remove();
            }
            download.button.disabled = false;
            download.button.innerHTML = '<i class="bi bi-download"></i> Download';
            downloadManager.removeDownload(download.id);
        }, 3000);

    } catch (error) {
        failDownload(download, 'Failed to save file: ' + error.message);
    }
}

function failDownload(download, errorMessage) {
    const progressBar = download.progressContainer.querySelector('.progress-bar');
    progressBar.classList.remove('progress-bar-animated');
    progressBar.classList.add('error');
    updateDownloadStatus(download, errorMessage);
    
    showMessage('Download failed: ' + errorMessage, 'danger');
    
    // Reset button after delay
    setTimeout(() => {
        if (download.progressContainer) {
            download.progressContainer.remove();
        }
        download.button.disabled = false;
        download.button.innerHTML = '<i class="bi bi-download"></i> Download';
        downloadManager.removeDownload(download.id);
    }, 5000);
}

function updateProgress(download, loaded, total, speed = '') {
    const percentage = total ? Math.floor((loaded / total) * 100) : 0;
    const progressBar = download.progressContainer.querySelector('.progress-bar');
    const percentageElement = download.progressContainer.querySelector('.download-percentage');
    const speedElement = download.progressContainer.querySelector('.download-speed');
    
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    percentageElement.textContent = percentage + '%';
    
    if (speed) {
        speedElement.textContent = speed;
    }
}

function updateDownloadStatus(download, status) {
    const statusElement = download.progressContainer.querySelector('.download-status');
    statusElement.textContent = status;
}

function formatSpeed(bytesPerSecond) {
    const units = ['B/s', 'KB/s', 'MB/s', 'GB/s'];
    let size = bytesPerSecond;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return size.toFixed(1) + ' ' + units[unitIndex];
}

// Enhanced device/OS detection
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

// Simple location detection using timezone only
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