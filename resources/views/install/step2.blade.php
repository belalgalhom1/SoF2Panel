@extends('install.layout')

@section('title', 'Create Admin')

@section('content')
    <h2 class="subtitle">Step 2: Create Admin Account</h2>
    
    <form method="POST" action="{{ route('install.step2') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-input" value="{{ old('username') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required>
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
@endsection
