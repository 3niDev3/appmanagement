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
                <!-- User Welcome -->
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


                    <form action="{{ route('project.uploadStore', $project->slug) }}" method="POST" enctype="multipart/form-data" class="dropzone-form">
                        @csrf

                        <!-- Description field -->
                        <div class="mb-3 text-start">
                            <label for="description" class="form-label fw-semibold">Description (optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Add a brief description for this APK">{{ old('description') }}</textarea>
                        </div>

                        <div class="dropzone mb-3 p-5 rounded-3" id="apkDropzone">
                            <i class="bi bi-cloud-arrow-up fs-1 mb-3"></i>
                            <div id="dropzoneText">Click or drag file here</div>
                            <input type="file" name="apk_file" class="form-control d-none" id="apkInput" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">Upload APK</button>
                    </form>

                    {{-- <a href="{{ route('project.list', $project->slug) }}" class="d-block mt-3 text-decoration-none text-muted">
                        <i class="bi bi-arrow-left"></i> Back to APKs
                    </a> --}}
                </div>
            </div>
        </div>
    @endif

</div>

<script>
const dropzone = document.getElementById('apkDropzone');
const input = document.getElementById('apkInput');
const dropText = document.getElementById('dropzoneText');

dropzone.addEventListener('click', () => input.click());
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.style.background = '#e9ecef'; });
dropzone.addEventListener('dragleave', e => { e.preventDefault(); dropzone.style.background = '#f1f3f5'; });
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if(file){ input.files = e.dataTransfer.files; dropText.innerText = file.name; }
});
</script>
@endsection
