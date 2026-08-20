@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3 style="margin: 0;">Activity Logs</h3>
    </div>

    <div class="table-responsive">
        <table class="panel-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Target</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space: nowrap; font-family: monospace;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user ? $log->user->username : 'System / Guest' }}</td>
                    <td>
                        <span class="status-badge" style="background: var(--surface); border: 1px solid var(--border);">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td>{{ $log->target }}</td>
                    <td style="font-family: monospace; color: var(--text-muted);">{{ $log->ip }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No activity logs recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>

    <div style="margin-top: 1rem;">
        {{ $logs->links('pagination::simple-bootstrap-4') }}
    </div>
</div>
@endsection
