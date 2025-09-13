@extends('layouts.apk')

@section('title', 'Upload APK - ' . $project->name)

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

                        <form method="POST" action="{{ route('project.loginAndUpload', $project->slug) }}">
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
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-12">
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

                <div class="upload-card p-5 text-center">
                    <h2 class="mb-3 fw-bold">Upload APK for <br>{{ $project->name }}</h2>
                    <p class="mb-4 text-muted">Drag & drop your APK file here or click to select</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form id="uploadForm" action="{{ route('project.uploadStore', $project->slug) }}" method="POST" enctype="multipart/form-data" class="dropzone-form">
                        @csrf
                        
                        <!-- Description field -->
                        <div class="mb-3 text-start">
                            <label for="description" class="form-label fw-semibold">Description (optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Add a brief description for this APK">{{ old('description') }}</textarea>
                        </div>

                        <!-- File Drop Zone -->
                        <div class="dropzone mb-3 p-5 rounded-3" id="apkDropzone">
                            <i class="bi bi-cloud-arrow-up fs-1 mb-3"></i>
                            <div id="dropzoneText">Click or drag file here</div>
                            <input type="file" name="apk_file" class="form-control d-none" id="apkInput" accept=".apk,.zip" required>
                        </div>

                        <!-- Selected File Info -->
                        <div id="fileInfo" class="file-info text-start" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-zip text-primary me-2"></i>
                                <div>
                                    <div id="fileName" class="fw-semibold"></div>
                                    <div id="fileSize" class="small text-muted"></div>
                                </div>
                                <button type="button" id="removeFile" class="btn btn-sm btn-outline-danger ms-auto">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Progress Container -->
                        <div id="progressContainer" class="progress-container" style="display: none;">
                            <div class="progress">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span id="progressText">0%</span>
                                </div>
                            </div>
                            <div id="uploadStatus" class="upload-status text-center">
                                <div id="statusText">Preparing upload...</div>
                                <div id="speedText" class="speed-info"></div>
                            </div>
                        </div>

                        <button type="submit" id="uploadBtn" class="btn btn-primary w-100 py-2 rounded-pill">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Upload APK
                        </button>
                        
                        <button type="button" id="cancelBtn" class="btn btn-outline-secondary w-100 py-2 rounded-pill mt-2" style="display: none;">
                            <i class="bi bi-x-circle me-2"></i>Cancel Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
