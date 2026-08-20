@extends('layouts.app')

@section('title', 'Error')

@section('content')
    <div style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
        <div class="glass-panel" style="text-align: center; width: 100%; max-width: 500px;">
            <i data-feather="alert-triangle" style="width: 64px; height: 64px; color: var(--danger); margin-bottom: 1.5rem;"></i>
            <h1 style="margin-bottom: 0.5rem; font-size: 2rem;">@yield('code')</h1>
            <h2 style="margin-bottom: 1.5rem; color: var(--text-muted);">@yield('message')</h2>
            <a href="{{ url('/') }}" class="btn btn-primary" style="width: auto; display: inline-block;">Return Home</a>
        </div>
    </div>
@endsection
