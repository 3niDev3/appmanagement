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
    </style>
    <style>
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
            <a class="navbar-brand" href="#">APK Manager</a>
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
