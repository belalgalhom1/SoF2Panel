@extends('layouts.app')

@section('title', 'Import Server')

@section('content')
    <div style="margin-bottom: 2rem;">
        <h2 style="margin: 0;">Import Existing Server</h2>
        <p class="subtitle" style="text-align: left; margin-bottom: 0;">Take over a manually-installed server without losing any files.</p>
    </div>

    <div class="glass-panel wide">
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 6px; border: 1px solid var(--border); font-size: 0.9rem; color: var(--text-muted);">
            <strong>How it works:</strong>
            files are <strong>copied</strong> (never moved or deleted) from the source folder
            into a new panel-managed user. The source stays untouched — delete it manually once the import works.
            Any process still running from that folder is stopped first so ports are free.
            Needs ~2x disk space during the copy.
        </div>

        <form method="POST" action="{{ route('servers.import.store') }}">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Server Name</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Host Server</label>
                    <select name="host_id" class="form-input" required>
                        @foreach($hosts as $host)
                            <option value="{{ $host->id }}" {{ old('host_id') == $host->id || (is_null(old('host_id')) && $loop->first) ? 'selected' : '' }}>{{ $host->name }} ({{ $host->hostname }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Game Type</label>
                    <select name="game_id" class="form-input" required>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}" {{ old('game_id') == $game->id || (is_null(old('game_id')) && $loop->first) ? 'selected' : '' }}>{{ $game->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Source Folder (absolute path to your existing server files)</label>
                    <input type="text" name="source_path" class="form-input" value="{{ old('source_path') }}" placeholder="e.g. /home/test1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New FTP Username (created by panel, must not exist yet)</label>
                    <input type="text" name="ftp_username" class="form-input" value="{{ old('ftp_username') }}" placeholder="e.g. sof2_1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Server Port</label>
                    <input type="number" name="port" class="form-input" value="{{ old('port', '20100') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Server Port (Gold)</label>
                    <input type="number" name="port_gold" class="form-input" value="{{ old('port_gold') }}" placeholder="Optional">
                </div>

                <div class="form-group">
                    <label class="form-label">Max Clients</label>
                    <input type="number" name="max_clients" class="form-input" value="{{ old('max_clients', '32') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">FTP Password (Optional)</label>
                    <input type="text" name="ftp_password" class="form-input" value="{{ old('ftp_password') }}">
                    <small style="color: var(--text-muted);">Leave empty to auto-generate.</small>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">RCON Password</label>
                    <input type="text" name="rcon_password" class="form-input" value="{{ old('rcon_password') }}" required>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="width: auto;">Import Server</button>
                <a href="{{ route('servers.index') }}" class="btn" style="color: var(--text-muted); border: 1px solid var(--border);">Cancel</a>
            </div>
        </form>
    </div>
@endsection
