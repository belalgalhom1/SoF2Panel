@extends('layouts.app')

@section('title', 'Add Game')

@section('content')
    <div style="margin-bottom: 2rem;">
        <h2 style="margin: 0;">Add New Game</h2>
    </div>

    <div class="glass-panel wide">
        <form method="POST" action="{{ route('games.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Game Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g. Soldier of Fortune II" required>
            </div>

            <div class="form-group">
                <label class="form-label">Base Location</label>
                <input type="text" name="base_location" class="form-input" value="{{ old('base_location', '/usr/games/sof2') }}" placeholder="Absolute path on the host to copy files from" required>
            </div>

            <div class="form-group">
                <label class="form-label">Start Script</label>
                <textarea name="start_script" class="form-input" style="min-height: 150px; font-family: monospace;" required>{{ old('start_script') }}</textarea>
                <small style="color: var(--text-muted); margin-top: 0.5rem; display: block;">Available variables: {server_port}, {server_port_gold}, {max_clients}, {rconpassword}, {server_account}, {server_name}</small>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="width: auto;">Save Game</button>
                <a href="{{ route('games.index') }}" class="btn" style="color: var(--text-muted); border: 1px solid var(--border);">Cancel</a>
            </div>
        </form>
    </div>
@endsection
