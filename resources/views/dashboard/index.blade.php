@extends('layouts.app')

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="server"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $serversCount }}</h3>
                <p>Servers</p>
            </div>
        </div>
        
        @if(auth()->user()->isAdmin())
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--secondary); background: rgba(236, 72, 153, 0.1);">
                <i data-feather="hard-drive"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $hostsCount }}</h3>
                <p>Host Nodes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: var(--success); background: rgba(16, 185, 129, 0.1);">
                <i data-feather="users"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $usersCount }}</h3>
                <p>Total Users</p>
            </div>
        </div>
        @endif
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3>Recent Activity</h3>
        </div>
        <div class="table-responsive">
        <table class="panel-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="font-weight: 500;">{{ $log->user->username ?? $log->user->email ?? 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->target }}</td>
                        <td style="color: var(--text-muted);">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No recent activity.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
