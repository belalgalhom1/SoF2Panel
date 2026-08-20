@extends('layouts.app')

@section('title', 'Add Host')

@section('content')
    <div style="margin-bottom: 2rem;">
        <h2 style="margin: 0;">Add New Host Node</h2>
        <p class="subtitle" style="text-align: left; margin-bottom: 0;">Provide SSH credentials for the new physical server.</p>
    </div>

    <div class="glass-panel wide">
        <form method="POST" action="{{ route('hosts.store') }}">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Host Name (Internal Alias)</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g. Frankfurt Node 1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Hostname or IP Address</label>
                    <input type="text" name="hostname" class="form-input" value="{{ old('hostname') }}" placeholder="e.g. 192.168.1.50" required>
                </div>

                <div class="form-group">
                    <label class="form-label">SSH Port</label>
                    <input type="number" name="port" class="form-input" value="{{ old('port', '22') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">SSH Username</label>
                    <input type="text" name="username" class="form-input" value="{{ old('username', 'root') }}" required>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">SSH Password</label>
                    <input type="password" name="password" class="form-input" required>
                    <small style="color: var(--text-muted); margin-top: 0.5rem; display: block;">The password will be encrypted in the database. The connection will be verified before saving.</small>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="width: auto;">Verify & Save Host</button>
                <a href="{{ route('hosts.index') }}" class="btn" style="color: var(--text-muted); border: 1px solid var(--border);">Cancel</a>
            </div>
        </form>
    </div>
@endsection
