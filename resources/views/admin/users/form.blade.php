@extends('admin.layouts.admin')

@section('content')
<div class="content">
    <div class="form-admin">
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
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" 
                   required style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" 
                   required style="background-color:#222; color:#fff; border:1px solid #555;">
        </div>

        <div class="mb-3">
            <label class="form-label text-white">
                {{ $isEdit ? 'New Password' : 'Password' }}
            </label>
            <div class="password-container">
                <input type="password" 
                    name="password" 
                    id="password" 
                    class="form-control" 
                    autocomplete="new-password"
                    {{ !$isEdit ? 'required' : '' }}
                    style="background-color:#222; color:#fff; border:1px solid #555;">
                <button type="button" class="password-toggle" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-white">
                {{ $isEdit ? 'Confirm New Password' : 'Confirm Password' }}
            </label>
            <div class="password-container">
                <input type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    class="form-control" 
                    autocomplete="new-password"
                    style="background-color:#222; color:#fff; border:1px solid #555;">
                <button type="button" class="password-toggle" data-target="password_confirmation">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
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

        <div class="mb-3 form-check">
            <input type="hidden" name="can_upload" value="0"> 
            <input type="checkbox"
                class="form-check-input"
                id="can_upload"
                name="can_upload"
                value="1"
                {{ old('can_upload', $user->can_upload ?? 0) == 1 ? 'checked' : '' }}>
            <label class="form-check-label text-white" for="can_upload">Allow user to upload URLs</label>
        </div>




        <button type="submit" class="btn btn-dark">{{ $isEdit ? 'Update User' : 'Create User' }}</button>
    </form>
    </div>
</div>

@endsection

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { background-color: #111; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: #777; }
        .btn-dark { background: linear-gradient(45deg, #ff6b6b, #4ecdc4); border-color: #444; color: #fff; }
        .btn-dark:hover { background-color: #222; }
        .alert-success { background-color: #28a745; border-color: #1e7e34; color: #fff; }
        .alert-danger { background-color: #dc3545; border-color: #bd2130; color: #fff; }
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

        /* Password input container */
        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #aaa;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #fff;
        }

        .password-toggle:focus {
            outline: none;
            color: #4ecdc4;
        }

        /* Adjust input padding to accommodate the eye icon */
        .password-container input {
            padding-right: 40px !important;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        .spinner-border {
            display: inline-block;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }

        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-valid {
            border-color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        /* Remove Bootstrap validation icons */
        .form-control.is-valid,
        .form-control.is-invalid {
            background-image: none !important;
            padding-right: 40px !important; /* Keep space for your eye icon */
        }



    </style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            function formatOption(option) {
                if (!option.id) return option.text;
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

            function applyTagColors() {
                $('#projectsSelect').next('.select2-container')
                    .find('.select2-selection__choice')
                    .each(function() {
                        var optionId = $(this).data('data').id;
                        var color = $('#projectsSelect option[value="'+optionId+'"]').data('color');
                        if (color) {
                            $(this).css('background-color', color);
                        }
                    });
            }
            setTimeout(applyTagColors, 100);
            $('#projectsSelect').on('select2:select select2:unselect', applyTagColors);

            // Password toggle
            $('.password-toggle').on('click', function() {
                const targetId = $(this).data('target');
                const passwordInput = $('#' + targetId);
                const icon = $(this).find('i');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Require confirm password only if password is filled
            $('input[name="password"]').on('input', function() {
                const passwordConfirm = $('input[name="password_confirmation"]');
                if ($(this).val().length > 0) {
                    passwordConfirm.attr('required', true);
                } else {
                    passwordConfirm.removeAttr('required');
                    passwordConfirm.val('');
                    passwordConfirm.removeClass('is-valid is-invalid');
                }
            });

            // Real-time match check
            function checkPasswordMatch() {
                const password = $('input[name="password"]').val();
                const passwordConfirm = $('input[name="password_confirmation"]').val();
                const confirmField = $('input[name="password_confirmation"]');

                if (password && passwordConfirm) {
                    if (password === passwordConfirm) {
                        confirmField.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        confirmField.removeClass('is-valid').addClass('is-invalid');
                    }
                } else {
                    confirmField.removeClass('is-valid is-invalid');
                }
            }
            $('input[name="password"], input[name="password_confirmation"]').on('input', checkPasswordMatch);

            // ✅ Form submission handling (only validation, no button disable/enable)
            $('form').on('submit', function(e) {
                const password = $('input[name="password"]').val();
                const passwordConfirm = $('input[name="password_confirmation"]').val();

                $('.password-error-msg').remove();

                if (password && password !== passwordConfirm) {
                    e.preventDefault();
                    $('input[name="password_confirmation"]').addClass('is-invalid');
                    $('input[name="password_confirmation"]').after(
                        '<div class="password-error-msg text-danger mt-1 small">Passwords do not match</div>'
                    );
                    return false;
                }
            });

            // Clear error on focus
            $('input[name="password"], input[name="password_confirmation"]').on('focus', function() {
                $('.password-error-msg').remove();
                $(this).removeClass('is-invalid');
            });
        });

    </script>
@endpush