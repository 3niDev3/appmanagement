@extends('admin.layouts.admin')

@section('title', isset($project) ? 'Edit Project' : 'Create Project')

@section('content')
    <div class="content py-5">
        <div class="page-header">
            <h1 class="page-title">{{ isset($project) ? 'Edit Project' : 'Create Project' }}</h1>
        </div>
        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($project) ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}" method="POST">
            @csrf
            @if(isset($project))
                @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="name" class="form-label text-white">
                        Project Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                            name="name" 
                            class="form-control form-control-dark" 
                            id="name" 
                            value="{{ old('name', $project->name ?? '') }}" 
                            required 
                            placeholder="e.g., Test User, My Awesome Project"
                            autocomplete="off">
                    <div class="form-text text-light">
                        <small>Enter the project name. Slug will be auto-generated.</small>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="slug" class="form-label text-white">
                        URL Slug <span class="text-danger">*</span>
                        <button type="button" class="btn btn-sm btn-outline-info ms-2" id="generateSlugBtn">
                            <i class="fas fa-sync-alt"></i> Auto Generate
                        </button>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-secondary text-white border-secondary">
                            {{ url('/') }}/
                        </span>
                        <input type="text" 
                                name="slug" 
                                class="form-control form-control-dark" 
                                id="slug" 
                                value="{{ old('slug', $project->slug ?? '') }}" 
                                required 
                                placeholder="e.g., test-user, my-awesome-project"
                                pattern="[a-z0-9\-]+"
                                autocomplete="off">
                    </div>
                    <div id="slugPreview" class="mt-2" style="display: none;">
                        <small class="text-info">
                            <strong>Project URLs:</strong><br>
                            <i class="fas fa-list me-1"></i> <strong>List Page:</strong> 
                            <span class="text-warning" id="slugPreviewListUrl"></span><br>
                            <i class="fas fa-upload me-1"></i> <strong>Upload Page:</strong> 
                            <span class="text-warning" id="slugPreviewUploadUrl"></span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-dark">{{ isset($project) ? 'Update Project' : 'Create Project' }}</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-dark">Cancel</a>
            </div>
        </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    body { background-color: #111; }
    .form-control:focus { box-shadow: none; border-color: #777; background-color:#222; color:#fff; }
    .btn-dark { background: linear-gradient(45deg, #ff6b6b, #4ecdc4); border-color: #444; color: #fff; }
    .btn-dark:hover { background-color: #fff; color: #000; }
    .btn-outline-light { color: #fff; border-color: #777; }
    .btn-outline-light:hover { background-color: #222; border-color: #fff; }
    .alert-success { background-color: #333; border-color: #444; color: #fff; }
    .alert-danger { background-color: #333; border-color: #444; color: #fff; }
    label.text-white { font-weight: 600; }
    small.text-light { color: #aaa; }

    /* If you want to use select2 in future for project colors */
    .select2-container--classic .select2-selection--multiple .select2-selection__choice {
        padding: 2px 6px;
        border-radius: 4px;
        color: #fff !important;
        border: none;
        margin-right: 5px;
        background: black;
    }
    .select2-container--classic .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
        margin-right: 4px;
        cursor: pointer;
    }

    .text-info{
        color: #fff !important;
    }
    
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const generateSlugBtn = document.getElementById('generateSlugBtn');
    const slugPreview = document.getElementById('slugPreview');
    const slugPreviewListUrl = document.getElementById('slugPreviewListUrl');
    const slugPreviewUploadUrl = document.getElementById('slugPreviewUploadUrl');
    const submitBtn = document.getElementById('submitBtn');
    const projectForm = document.getElementById('projectForm');
    
    let isManualSlug = false;
    let debounceTimer = null;
    
    // Auto-generate slug when typing name
    nameInput.addEventListener('input', function() {
        if (!isManualSlug) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                generateSlugFromName(this.value);
            }, 300);
        }
    });
    
    // Detect manual slug editing
    slugInput.addEventListener('input', function() {
        isManualSlug = true;
        validateSlug(this.value);
        updateSlugPreview(this.value);
    });
    
    // Manual slug generation button
    generateSlugBtn.addEventListener('click', function() {
        const name = nameInput.value.trim();
        if (name) {
            generateSlugFromName(name, true);
            isManualSlug = false;
        } else {
            alert('Please enter a project name first');
            nameInput.focus();
        }
    });
    
    // Generate slug from name
    function generateSlugFromName(name, force = false) {
        if (!name.trim()) {
            slugInput.value = '';
            updateSlugPreview('');
            return;
        }
        
        // Simple client-side slug generation
        let slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .trim('-'); // Remove leading/trailing hyphens
        
        slugInput.value = slug;
        updateSlugPreview(slug);
        validateSlug(slug);
        
        // Optional: Check uniqueness via AJAX
        checkSlugUniqueness(slug);
    }
    
    // Validate slug format
    function validateSlug(slug) {
        const slugPattern = /^[a-z0-9\-]+$/;
        
        if (slug && !slugPattern.test(slug)) {
            slugInput.classList.add('slug-invalid');
            slugInput.classList.remove('slug-valid');
        } else if (slug) {
            slugInput.classList.add('slug-valid');
            slugInput.classList.remove('slug-invalid');
        } else {
            slugInput.classList.remove('slug-valid', 'slug-invalid');
        }
    }
    
    // Update slug preview with both list and upload URLs
    function updateSlugPreview(slug) {
        if (slug) {
            const baseUrl = '{{ url("/") }}';
            slugPreviewListUrl.textContent = baseUrl + '/' + slug + '/list';
            slugPreviewUploadUrl.textContent = baseUrl + '/' + slug + '/upload';
            slugPreview.style.display = 'block';
        } else {
            slugPreview.style.display = 'none';
        }
    }
    
    // Check slug uniqueness (optional)
    function checkSlugUniqueness(slug) {
        if (!slug) return;
        
        // You can implement AJAX call here to check uniqueness
        // For now, just simulate with a timeout
        slugInput.classList.add('slug-generating');
        
        setTimeout(() => {
            slugInput.classList.remove('slug-generating');
            // Simulate uniqueness check result
            validateSlug(slug);
        }, 500);
    }
    
    // Form submission validation
    projectForm.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        const slug = slugInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            alert('Project name is required');
            nameInput.focus();
            return;
        }
        
        if (!slug) {
            e.preventDefault();
            alert('Slug is required');
            slugInput.focus();
            return;
        }
        
        if (!/^[a-z0-9\-]+$/.test(slug)) {
            e.preventDefault();
            alert('Slug can only contain lowercase letters, numbers, and hyphens');
            slugInput.focus();
            return;
        }
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    });
    
    // Initialize on page load
    const initialSlug = slugInput.value;
    if (initialSlug) {
        updateSlugPreview(initialSlug);
        validateSlug(initialSlug);
        isManualSlug = true;
    }
});
</script>
@endpush