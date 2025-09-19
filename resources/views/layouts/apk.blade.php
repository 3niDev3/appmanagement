<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'APK Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
     <style>
        body {
            background-color: #f4f6f9;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
        }
        /* Navbar */
        .navbar {
            background-color: #0d6efd;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: #fff;
            font-weight: 600;
        }
        .navbar-brand:hover {
            color: #e2e6ea;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
            font-weight: 500;
        }
        .navbar-nav .nav-link:hover {
            color: #e2e6ea !important;
        }
        .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
        }
        .dropdown-item {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .dropdown-divider {
            margin: 4px 0;
        }
        .user-info {
            background-color: #e3f2fd;
            color: #0d6efd;
            font-weight: 500;
        }
        .btn-logout {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            font-size: 0.875rem;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .btn-logout:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid #dee2e6;
        }
        /* Cards */
        .apk-card, .upload-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .apk-card:hover, .upload-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        /* Buttons */
        .btn-primary, .btn-upload {
            border-radius: 50px;
            font-weight: 600;
        }
        /* Dropzone */
        .dropzone {
            background: #f1f3f5;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s, border-color 0.3s;
        }
        .dropzone:hover {
            background: #e9ecef;
            border-color: #0d6efd;
        }
        /* Badge */
        .badge-new {
            background-color: #e9ecef;
            color: #333;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Project selector styles */
        .project-selector {
            min-width: 200px;
        }
        
        .current-project {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 500;
        }
        
        .current-project:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
        }
        
        .project-dropdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .project-dropdown-item i {
            opacity: 0.6;
        }
        
        .navbar-collapse {
            flex-grow: 0;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                padding-top: 1rem;
            }
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                box-shadow: none;
                border: 1px solid #dee2e6;
                margin-top: 0.5rem;
            }
            .project-selector {
                min-width: 100%;
            }
        }

        .upload-card {
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .dropzone {
            border: 2px dashed #6c757d;
            background-color: #f1f3f5;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone:hover, .dropzone.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }

        .progress-container {
            margin-top: 20px;
        }

        .progress {
            height: 25px;
            border-radius: 15px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 15px;
            transition: width 0.3s ease;
        }

        .upload-status {
            margin-top: 10px;
            font-size: 14px;
        }

        .speed-info {
            font-size: 12px;
            color: #6c757d;
        }

        .file-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
     <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-phone me-2"></i>APK Manager
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @if(Auth::guard('web')->check() || Auth::guard('admin')->check())
                        @php
                            $currentUser = Auth::guard('web')->check() ? Auth::guard('web')->user() : Auth::guard('admin')->user();
                            $isAdmin = Auth::guard('admin')->check();
                        @endphp
                        
                        <!-- Project Selector Dropdown -->
                        @if(isset($userProjects) && $userProjects->count() > 0)
                        <li class="nav-item dropdown me-3">
                            <button class="btn current-project dropdown-toggle project-selector" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-folder me-1"></i>
                                @if(isset($project))
                                    {{ Str::limit($project->name, 20) }}
                                @else
                                    Select Project
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header"><i class="bi bi-building me-1"></i>Available Projects</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach($userProjects as $proj)
                                <li>
                                    <a class="dropdown-item project-dropdown-item" href="{{ route('project.list', $proj->slug) }}">
                                        <span>
                                            <i class="bi bi-folder me-2"></i>{{ $proj->name }}
                                        </span>
                                        @if(isset($project) && $project->id == $proj->id)
                                            <i class="bi bi-check-circle text-success"></i>
                                        @endif
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        
                        <!-- User Info & Actions -->
                        <li class="nav-item dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ Str::limit($currentUser->name, 15) }}
                                @if($isAdmin)
                                    <span class="badge bg-warning text-dark ms-1">Admin</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-item user-info">
                                        <i class="bi bi-person me-2"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $currentUser->name }}</div>
                                            <small class="text-muted">{{ $currentUser->email }}</small>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                @if(isset($project))
                                <li>
                                    <a class="dropdown-item" href="{{ route('project.list', $project->slug) }}">
                                        <i class="bi bi-list me-2"></i>View APKs
                                    </a>
                                </li>
                                @if($isAdmin || (Auth::guard('web')->check() && Auth::guard('web')->user()->can_upload))
                                <li>
                                    <a class="dropdown-item" href="{{ route('project.uploadForm', $project->slug) }}">
                                        <i class="bi bi-upload me-2"></i>Upload APK
                                    </a>
                                </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                @endif
                                
                                <li>
                                    <form method="POST" action="{{ isset($project) ? route('project.logout', $project->slug) : route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        @yield('content')
    </main>

    <footer class="py-3 text-center">
        &copy; {{ date('Y') }} APK Manager. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('apkDropzone');
    const input = document.getElementById('apkInput');
    const dropText = document.getElementById('dropzoneText');
    const form = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const statusText = document.getElementById('statusText');
    const speedText = document.getElementById('speedText');
    const uploadBtn = document.getElementById('uploadBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    
    let currentXHR = null;
    let startTime = 0;

    // File selection events
    dropzone.addEventListener('click', () => input.click());
    dropzone.addEventListener('dragover', handleDragOver);
    dropzone.addEventListener('dragleave', handleDragLeave);
    dropzone.addEventListener('drop', handleDrop);
    input.addEventListener('change', handleFileSelect);
    removeFile.addEventListener('click', clearFile);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const file = input.files[0];
        if (!file) {
            alert('Please select a file to upload');
            return;
        }

        startUpload();
    });

    // Cancel upload
    cancelBtn.addEventListener('click', function() {
        if (currentXHR) {
            currentXHR.abort();
        }
    });

    function handleDragOver(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            input.files = e.dataTransfer.files;
            showFileInfo(file);
        }
    }

    function handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            showFileInfo(file);
        }
    }

    function showFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'block';
        dropText.textContent = 'File selected: ' + file.name;
    }

    function clearFile() {
        input.value = '';
        fileInfo.style.display = 'none';
        dropText.textContent = 'Click or drag file here';
        resetProgress();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function startUpload() {
        const formData = new FormData(form);
        showProgress();
        startTime = Date.now();

        currentXHR = new XMLHttpRequest();

        // Upload progress
        currentXHR.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                updateProgress(percentComplete, e.loaded, e.total);
            }
        });

        // Upload complete
        currentXHR.addEventListener('load', function() {
            if (currentXHR.status === 200) {
                uploadSuccess();
            } else {
                uploadError('Upload failed with status: ' + currentXHR.status);
            }
        });

        // Upload error
        currentXHR.addEventListener('error', function() {
            uploadError('Upload failed due to network error');
        });

        // Upload aborted
        currentXHR.addEventListener('abort', function() {
            uploadCancelled();
        });

        // Send request
        currentXHR.open('POST', form.action);
        currentXHR.send(formData);
    }

    function showProgress() {
        progressContainer.style.display = 'block';
        uploadBtn.style.display = 'none';
        cancelBtn.style.display = 'block';
        input.disabled = true;
    }

    function updateProgress(percent, loaded, total) {
        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', percent);
        progressText.textContent = percent + '%';
        
        // Calculate upload speed
        const elapsed = (Date.now() - startTime) / 1000;
        const speed = loaded / elapsed;
        const remaining = (total - loaded) / speed;
        
        statusText.textContent = `Uploading... ${formatFileSize(loaded)} of ${formatFileSize(total)}`;
        speedText.textContent = `Speed: ${formatFileSize(speed)}/s • ETA: ${formatTime(remaining)}`;
    }

    function uploadSuccess() {
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
        statusText.textContent = 'Upload completed successfully!';
        speedText.textContent = 'File uploaded and processed';
        
        setTimeout(() => {
            window.location.href = "{{ route('project.list', $project->slug) }}";
        }, 2000);
    }

    function uploadError(message) {
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-danger');
        statusText.textContent = 'Upload failed!';
        speedText.textContent = message;
        resetUploadState();
    }

    function uploadCancelled() {
        statusText.textContent = 'Upload cancelled';
        speedText.textContent = '';
        resetProgress();
        resetUploadState();
    }

    function resetUploadState() {
        setTimeout(() => {
            uploadBtn.style.display = 'block';
            cancelBtn.style.display = 'none';
            input.disabled = false;
        }, 3000);
    }

    function resetProgress() {
        progressContainer.style.display = 'none';
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-success', 'bg-danger');
        progressBar.classList.add('progress-bar-animated');
        progressText.textContent = '0%';
        statusText.textContent = 'Preparing upload...';
        speedText.textContent = '';
    }

    function formatTime(seconds) {
        if (!isFinite(seconds) || seconds < 0) return '--:--';
        
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
});
</script>
</body>
</html>
