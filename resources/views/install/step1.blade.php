@extends('install.layout')

@section('title', 'Database Setup')

@section('content')
    <h2 class="subtitle">Step 1: Database Setup</h2>
    
    <form method="POST" action="{{ route('install.step1') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Database Host</label>
            <input type="text" name="db_host" class="form-input" value="{{ old('db_host', '127.0.0.1') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Database Port</label>
            <input type="number" name="db_port" class="form-input" value="{{ old('db_port', '3306') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Database Name</label>
            <input type="text" name="db_database" class="form-input" value="{{ old('db_database', 'sof2panel') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Database Username</label>
            <input type="text" name="db_username" class="form-input" value="{{ old('db_username', 'root') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Database Password</label>
            <input type="password" name="db_password" class="form-input">
        </div>

        <button type="submit" class="btn btn-primary">Test Connection & Setup Database</button>
    </form>
@endsection
