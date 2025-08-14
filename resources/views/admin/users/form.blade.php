@extends('admin.layouts.admin')

@section('content')
<div class="container py-5">
    @php
        $isEdit = isset($user);
    @endphp

    <h2 class="text-white mb-4">{{ $isEdit ? 'Edit User' : 'Add User' }}</h2>


    <form action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label text-white">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">{{ $isEdit ? 'Password (leave blank to keep current)' : 'Password' }}</label>
            <input type="password" name="password" class="form-control" style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">{{ $isEdit ? 'Confirm Password' : 'Confirm Password' }}</label>
            <input type="password" name="password_confirmation" class="form-control" style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">Assign Projects</label>
            <select name="projects[]" id="projectsSelect" class="form-select select2" multiple>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        data-color="{{ $project->color ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF)) }}" 
                        @if($isEdit && $user->projects->pluck('id')->contains($project->id)) selected @endif>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>


        </div>

        <button type="submit" class="btn btn-dark">{{ $isEdit ? 'Update User' : 'Create User' }}</button>
    </form>
</div>

@endsection

@push('styles')
    <style>
        body { background-color: #111; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: #777; }
        .btn-dark { background-color: #000; border-color: #444; color: #fff; }
        .btn-dark:hover { background-color: #222; }
        .alert-success { background-color: #333; border-color: #444; color: #fff; }
        .alert-danger { background-color: #333; border-color: #444; color: #fff; }
        label.text-white { font-weight: 600; }


        /* Style the selected tags */
        .select2-container--classic .select2-selection--multiple .select2-selection__choice {
            padding: 2px 6px;
            border-radius: 4px;
            color: #fff !important;
            border: none;
            margin-right: 5px;
            background: black
        }

        /* Style the close icon */
        .select2-container--classic .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 4px;
            cursor: pointer;
        }

    </style>
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        function formatOption(option) {
            if (!option.id) return option.text; // placeholder
            return option.text;
        }

        $('#projectsSelect').select2({
            theme: 'classic',
            placeholder: "Select Projects",
            width: '100%',
            allowClear: true,
            templateSelection: formatOption,
            templateResult: formatOption
        });

        // Apply colors to selected tags
        function applyTagColors() {
            $('#projectsSelect').next('.select2-container')
                .find('.select2-selection__choice')
                .each(function() {
                    var optionId = $(this).data('data').id;
                    var color = $('#projectsSelect option[value="'+optionId+'"]').data('color');
                    $(this).css('background-color', color);
                });
        }

        // Initial colors
        applyTagColors();

        // Update colors on select/unselect
        $('#projectsSelect').on('select2:select select2:unselect', applyTagColors);
    });
</script>

@endpush

