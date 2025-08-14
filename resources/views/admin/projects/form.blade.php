@extends('admin.layouts.admin')

@section('title', isset($project) ? 'Edit Project' : 'Create Project')

@section('content')
    <div class="container py-5">
        <h2 class="text-white mb-4">{{ isset($project) ? 'Edit Project' : 'Create Project' }}</h2>

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

            <div class="mb-3">
                <label for="name" class="form-label text-white">Project Name</label>
                <input type="text" name="name" class="form-control" id="name" 
                    value="{{ old('name', $project->name ?? '') }}" 
                    required 
                    placeholder="Enter project name"
                    style="background-color:#222; color:#fff; border:1px solid #555;">
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label text-white">Slug (optional)</label>
                <input type="text" name="slug" class="form-control" id="slug" 
                    value="{{ old('slug', $project->slug ?? '') }}" 
                    placeholder="Enter project slug or leave blank"
                    style="background-color:#222; color:#fff; border:1px solid #555;">
                <small class="text-light">If left blank, slug will be auto-generated from the name.</small>
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
    .btn-dark { background-color: #000; border-color: #444; color: #fff; }
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
</style>
@endpush
