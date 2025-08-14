@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="fade-in">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h2>Welcome back, Admin!</h2>
            <p>Here's what's happening with your store today.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    {{-- <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3></h3>
                    <p>Total Blog</p>
                </div>
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3></h3>
                    <p>Active Blog</p>
                </div>
                <div class="stat-icon users">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3></h3>
                    <p>Inactive Blog</p>
                </div>
                <div class="stat-icon orders">
                    <i class="fas fa-pause-circle"></i>
                </div>
            </div>
        </div>
    </div> --}}


</div>
<style>
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 12px 12px 0 0 !important;
}
</style>
@endsection