@extends('admin.layouts.admin')

@section('title', 'Projects')

@section('content')
<div class="content">
    <div class="page-header">
        <h1 class="page-title">Projects</h1>
        <a href="{{ route('admin.projects.create') }}" class="add-btn">
            <i class="fas fa-plus"></i>
            Add Project</a>
    </div>

    <div class="projects-container">
        <div class="table-header">
            <div>#</div>
            <div>Name</div>
            <div>URLs</div>
            <div>Created At</div>
            <div>Actions</div>
        </div>

        @forelse($projects as $project)
            <div class="project-row">
                <div class="project-id">
                    {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
                </div>
                <div class="project-name">{{ $project->name }}</div>
                <div class="project-urls">
                    <div class="url-group">
                        <div class="url-item">
                            <label class="url-label">
                                <i class="fas fa-list me-1"></i> List Page:
                            </label>
                            <div class="url-container">
                                <input type="text" 
                                       class="url-input" 
                                       value="{{ url('/' . $project->slug . '/list') }}" 
                                       readonly 
                                       id="list-url-{{ $project->id }}">
                                <button type="button" 
                                        class="copy-btn" 
                                        onclick="copyToClipboard('list-url-{{ $project->id }}', this)"
                                        title="Copy URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="url-item">
                            <label class="url-label">
                                <i class="fas fa-upload me-1"></i> Upload Page:
                            </label>
                            <div class="url-container">
                                <input type="text" 
                                       class="url-input" 
                                       value="{{ url('/' . $project->slug . '/upload') }}" 
                                       readonly 
                                       id="upload-url-{{ $project->id }}">
                                <button type="button" 
                                        class="copy-btn" 
                                        onclick="copyToClipboard('upload-url-{{ $project->id }}', this)"
                                        title="Copy URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="project-date">{{ $project->created_at->format('d M Y') }}</div>
                <div class="actions">
                    <a href="{{ route('admin.projects.edit', $project->id) }}" class="action-btn edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="d-inline-block delete-form">
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
                No projects found.
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $projects->links() }}
    </div>
</div>

<!-- Toast notification for copy feedback -->
<div id="copyToast" class="toast-notification">
    <i class="fas fa-check-circle me-2"></i>
    <span id="toastMessage">URL copied to clipboard!</span>
</div>
@endsection

@push('styles')
<style>
.project-urls {
    min-width: 400px;
    max-width: 500px;
}

.url-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.url-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.url-label {
    font-size: 11px;
    font-weight: 600;
    color: #ccc;
    margin: 0;
}

.url-container {
    display: flex;
    align-items: center;
    gap: 6px;
}

.url-input {
    flex: 1;
    background-color: #222;
    border: 1px solid #444;
    border-radius: 4px;
    color: #fff;
    padding: 4px 8px;
    font-size: 11px;
    font-family: 'Courier New', monospace;
    min-width: 0; /* Allow shrinking */
}

.url-input:focus {
    outline: none;
    border-color: #4ecdc4;
    background-color: #333;
}

.copy-btn {
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
    border: none;
    border-radius: 4px;
    color: white;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 10px;
    transition: all 0.2s ease;
    min-width: 32px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.copy-btn:hover {
    background: linear-gradient(45deg, #ff5252, #26a69a);
    transform: scale(1.05);
}

.copy-btn.copied {
    background: linear-gradient(45deg, #4caf50, #4caf50);
    animation: pulse 0.3s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Toast notification */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(45deg, #4caf50, #66bb6a);
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    z-index: 1000;
    transform: translateX(400px);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
}

.toast-notification.show {
    transform: translateX(0);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-header {
        grid-template-columns: 40px 1fr 2fr 80px 100px;
    }
    
    .project-row {
        grid-template-columns: 40px 1fr 2fr 80px 100px;
    }
    
    .project-urls {
        min-width: auto;
        max-width: none;
    }
    
    .url-container {
        flex-direction: column;
        gap: 4px;
    }
    
    .url-input {
        font-size: 10px;
    }
    
    .copy-btn {
        align-self: flex-end;
        min-width: 60px;
    }
}

/* Update existing table styles for better alignment */
.table-header {
    display: grid;
    grid-template-columns: 60px 200px 1fr 120px 150px;
    gap: 15px;
    align-items: center;
}

.project-row {
    display: grid;
    grid-template-columns: 60px 200px 1fr 120px 150px;
    gap: 15px;
    align-items: center;
}

.actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.main-content{
    backdrop-filter: none !important;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(button) {
    if (confirm('Are you sure?')) {
        button.closest('form').submit();
    }
}

function copyToClipboard(inputId, button) {
    const input = document.getElementById(inputId);
    const toast = document.getElementById('copyToast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Select and copy the text
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        // Try the modern clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.value).then(() => {
                showCopyFeedback(button, toast, toastMessage, 'URL copied to clipboard!');
            }).catch(() => {
                // Fallback to document.execCommand
                fallbackCopy(input, button, toast, toastMessage);
            });
        } else {
            // Fallback for older browsers
            fallbackCopy(input, button, toast, toastMessage);
        }
    } catch (err) {
        console.error('Copy failed:', err);
        showCopyFeedback(button, toast, toastMessage, 'Copy failed - please select and copy manually');
    }
}

function fallbackCopy(input, button, toast, toastMessage) {
    try {
        document.execCommand('copy');
        showCopyFeedback(button, toast, toastMessage, 'URL copied to clipboard!');
    } catch (err) {
        showCopyFeedback(button, toast, toastMessage, 'Copy failed - please select and copy manually');
    }
}

function showCopyFeedback(button, toast, toastMessage, message) {
    // Update button appearance
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('copied');
    
    // Show toast notification
    toastMessage.textContent = message;
    toast.classList.add('show');
    
    // Reset button after 1.5 seconds
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('copied');
    }, 1500);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add click event for URL inputs to select all text
document.addEventListener('DOMContentLoaded', function() {
    const urlInputs = document.querySelectorAll('.url-input');
    urlInputs.forEach(input => {
        input.addEventListener('click', function() {
            this.select();
        });
    });
});
</script>
@endpush