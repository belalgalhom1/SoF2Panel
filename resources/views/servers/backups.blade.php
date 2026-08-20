@extends('layouts.app')

@section('title', 'Backups: ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Backups</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">Manage incremental backups for {{ $server->name }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('servers.show', $server) }}" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--bg-card); width: auto;">
                <i data-feather="arrow-left" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Back to Server
            </a>
            
            <form action="{{ route('servers.backups.store', $server) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" style="width: auto;">
                    <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Create Backup
                </button>
            </form>
        </div>
    </div>

    <div class="glass-panel" style="width: 100%; max-width: none;">
        @if($backups->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <i data-feather="hard-drive" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No backups found</h3>
                <p>Create a backup to capture the current state of your server files.</p>
            </div>
        @else
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th style="text-align: left; padding: 1rem;">Backup Name</th>
                        <th style="text-align: left; padding: 1rem;">Logical Size</th>
                        <th style="text-align: left; padding: 1rem;">Created At</th>
                        <th style="text-align: right; padding: 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem; font-family: monospace;">{{ $backup->filename }}</td>
                        <td style="padding: 1rem;">{{ number_format($backup->size / 1048576, 2) }} MB</td>
                        <td style="padding: 1rem;">{{ $backup->created_at->format('Y-m-d H:i:s') }}</td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <form action="{{ route('servers.backups.restore', [$server, $backup]) }}" method="POST" onsubmit="return confirm('Are you sure you want to restore this backup? ALL current files on the server will be PERMANENTLY deleted and replaced with this backup.');">
                                    @csrf
                                    <button type="submit" class="btn" style="width: auto; background: var(--warning); color: #111; border: none; padding: 0.5rem 1rem;">Restore</button>
                                </form>
                                <form action="{{ route('servers.backups.destroy', [$server, $backup]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="width: auto; background: var(--danger); border: none; padding: 0.5rem 1rem;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 6px; border: 1px solid var(--border);">
                <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                    <i data-feather="info" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 0.25rem;"></i>
                    <strong>Storage Efficiency:</strong> Backups use Linux hard-linking technology. The "Logical Size" shows the full size of the backed up files, but the actual disk space consumed is only the difference between modified files!
                </p>
            </div>
        @endif
    </div>
@endsection
