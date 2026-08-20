@extends('layouts.app')
@section('title', 'Settings: ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Settings</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->name }} &bull; {{ $server->host->hostname }}:{{ $server->port }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('servers.show', $server) }}" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); width: auto;">Back to Server</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; max-width: 800px;">
        
        <!-- Auto Restart Settings -->
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem;">
                <div>
                    <h3 style="margin: 0;">Auto-Restart</h3>
                    <p class="subtitle" style="margin: 0.25rem 0 0 0; font-size: 0.85rem;">Automatically restart the server if it crashes or stops unexpectedly.</p>
                </div>
                <div>
                    <form action="{{ route('servers.auto_restart.toggle', $server) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: auto; background: {{ $server->auto_restart ? 'var(--danger)' : 'var(--success)' }};">
                            {{ $server->auto_restart ? 'Disable Auto-Restart' : 'Enable Auto-Restart' }}
                        </button>
                    </form>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Current Status:</span>
                @if($server->auto_restart)
                    <span class="badge badge-success">Enabled</span>
                @else
                    <span class="badge badge-danger" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">Disabled</span>
                @endif
            </div>
        </div>


        <!-- Auto Backup Settings -->
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 1rem;">Automated Backups</h3>
            <p class="subtitle" style="margin-top: 0; margin-bottom: 1rem; font-size: 0.85rem;">Automatically run space-saving incremental backups on a set schedule.</p>
            <form method="POST" action="{{ route('servers.backup_settings.update', $server) }}">
                @csrf
                <div style="display: flex; gap: 2rem; align-items: center;" class="flex-responsive">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label class="form-label" style="display: flex; align-items: center; cursor: pointer;">
                            <input type="hidden" name="auto_backup" value="0">
                            <input type="checkbox" name="auto_backup" value="1" {{ $server->auto_backup ? 'checked' : '' }} style="margin-right: 0.5rem; width: 18px; height: 18px; accent-color: var(--primary);">
                            <span style="font-weight: 500;">Enable Automatic Backups</span>
                        </label>
                    </div>
                    <div class="form-group" style="flex: 2; margin: 0;">
                        <label class="form-label">Backup Interval</label>
                        <select name="backup_interval" class="form-input" style="cursor: pointer;">
                            <option value="60" {{ $server->backup_interval == 60 ? 'selected' : '' }}>Every 1 Hour</option>
                            <option value="180" {{ $server->backup_interval == 180 ? 'selected' : '' }}>Every 3 Hours</option>
                            <option value="360" {{ $server->backup_interval == 360 ? 'selected' : '' }}>Every 6 Hours</option>
                            <option value="720" {{ $server->backup_interval == 720 ? 'selected' : '' }}>Every 12 Hours</option>
                            <option value="1440" {{ $server->backup_interval == 1440 ? 'selected' : '' }}>Every 24 Hours</option>
                            <option value="2880" {{ $server->backup_interval == 2880 ? 'selected' : '' }}>Every 2 Days</option>
                            <option value="4320" {{ $server->backup_interval == 4320 ? 'selected' : '' }}>Every 3 Days</option>
                            <option value="10080" {{ $server->backup_interval == 10080 ? 'selected' : '' }}>Every 1 Week</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save Backup Settings</button>
                </div>
            </form>
        </div>

        <!-- General Settings -->
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 1rem;">General Settings</h3>
            <form method="POST" action="{{ route('servers.general.update', $server) }}">
                @csrf
                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Server Name</label>
                        <input type="text" name="name" class="form-input" required maxlength="255" value="{{ $server->name }}">
                    </div>
                    <div class="form-group" style="max-width: 150px;">
                        <label class="form-label">Max Clients</label>
                        <input type="number" name="max_clients" class="form-input" required min="1" max="128" value="{{ $server->max_clients }}">
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save General Settings</button>
                    <p class="subtitle" style="font-size: 0.8rem; margin-top: 0.5rem;">Note: Changes to these settings only take effect after restarting the server manually.</p>
                </div>
            </form>
        </div>

        <!-- Connection Settings -->
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 1rem;">Connection Settings</h3>
            <form method="POST" action="{{ route('servers.connection.update', $server) }}">
                @csrf
                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="max-width: 300px;">
                        <label class="form-label">Server Port</label>
                        <input type="number" name="port" class="form-input" required min="1024" max="65535" value="{{ $server->port }}">
                    </div>
                    <div class="form-group" style="max-width: 300px;">
                        <label class="form-label">Server Port (Gold)</label>
                        <input type="number" name="port_gold" class="form-input" min="1024" max="65535" value="{{ $server->port_gold }}" placeholder="Optional">
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save Ports</button>
                    <p class="subtitle" style="font-size: 0.8rem; margin-top: 0.5rem;">Changing ports will automatically restart the server if it is currently running.</p>
                </div>
            </form>
        </div>

        <!-- Start Script Settings -->
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 1rem;">Start Script</h3>
            <form method="POST" action="{{ route('servers.script.update', $server) }}">
                @csrf
                <div class="form-group">
                    <p class="subtitle" style="font-size: 0.8rem; margin-bottom: 0.5rem;">Available variables: {server_port}, {server_port_gold}, {max_clients}, {rconpassword}, {server_account}, {server_name}</p>
                    <textarea name="start_script" class="form-input" style="font-family: monospace; min-height: 150px; background: rgba(0,0,0,0.2);" required>{{ $server->start_script ?? $server->game->start_script }}</textarea>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save Script</button>
                </div>
            </form>
        </div>
        
    </div>
@endsection
