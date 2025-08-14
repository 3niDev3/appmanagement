@extends('admin.layouts.admin')

@section('title', 'Projects')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-dark">Projects</h2>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">Add Project</a>
    </div>

    <div class="card bg-dark text-white">
        <div class="card-body p-0">
            <table class="table table-dark table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->slug }}</td>
                            <td>{{ $project->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $projects->links() }}
    </div>
</div>
@endsection
