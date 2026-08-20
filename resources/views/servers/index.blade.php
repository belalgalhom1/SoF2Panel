@extends('layouts.app')

@section('title', 'Servers')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Game Servers</h2>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('servers.create') }}" class="btn btn-primary" style="width: auto;">
            <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Create Server
        </a>
        @endif
    </div>

    <div class="server-grid" style="margin-bottom: 2rem;">
        @forelse($servers as $server)
            <div class="stat-card" style="display: flex; flex-direction: column; align-items: flex-start; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <div class="stat-icon" style="margin-right: 1rem;">
                            <i data-feather="server"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem;">{{ $server->name }}</h3>
                            <p style="margin: 0; color: var(--text-muted); font-size: 0.875rem;">{{ $server->game->name }}</p>
                        </div>
                    </div>
                    <span class="badge {{ $server->expected_state === 'online' ? 'badge-success' : 'badge-danger' }}">{{ $server->expected_state === 'online' ? 'Running' : 'Stopped' }}</span>
                </div>
                
                <div style="width: 100%; display: flex; justify-content: space-between; font-size: 0.875rem; color: var(--text-muted); padding-top: 1rem; border-top: 1px solid var(--border);">
                    <span><i data-feather="hard-drive" style="width: 14px; height: 14px;"></i> {{ $server->host->name }}</span>
                    <span><i data-feather="link" style="width: 14px; height: 14px;"></i> {{ $server->host->hostname }}:{{ $server->port }}</span>
                </div>

                <a href="{{ route('servers.show', $server) }}" class="btn" style="width: 100%; background: rgba(99, 102, 241, 0.1); color: var(--primary); text-align: center;">Manage Server</a>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem; background: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border);">
                <i data-feather="frown" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>No servers found.</p>
            </div>
        @endforelse
    </div>
@endsection
