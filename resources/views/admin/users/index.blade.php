@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
<div class="content">
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <a href="{{ route('admin.users.create') }}" class="add-btn">
            <i class="fas fa-plus"></i>
            Add User
        </a>
    </div>

    <div class="projects-container"> {{-- reusing same container styles --}}
        <div class="table-header">
            <div>ID</div>
            <div>Name</div>
            <div>Email</div>
            <div>Projects</div>
            <div>Created At</div>
            <div>Actions</div>
        </div>

        @forelse($users as $user)
            <div class="project-row"> {{-- same row style --}}
                <div class="project-id">{{ $loop->iteration }}</div>
                <div class="project-name">{{ $user->name }}</div>
                <div class="project-name">{{ $user->email }}</div>
                <div class="project-name">{{ $user->projects->pluck('name')->implode(', ') }}</div>
                <div class="project-date">{{ $user->created_at->format('d M Y') }}</div>
                <div class="actions">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline-block delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="action-btn delete-btn" onclick="confirmDelete(this)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="no-data">
                No Users found.
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(button) {
        if (confirm('Are you sure?')) {
            button.closest('form').submit();
        }
    }
</script>
@endpush
@push('styles')
    <style>
        .table-header,
        .project-row {
            display: grid;
            grid-template-columns:
                80px             
                minmax(180px, 1fr) 
                minmax(200px, 1.5fr) 
                minmax(140px, 1fr)
                150px             
                180px;             
            gap: 2rem;
            align-items: center;
        }

        .table-header {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem 2rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .project-row {
            padding: 1.5rem 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
        }

    </style>
@endpush
