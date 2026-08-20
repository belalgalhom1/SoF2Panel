@extends('install.layout')

@section('title', 'Installation Complete')

@section('content')
    <div style="text-align: center; margin-bottom: 2rem;">
        <svg style="width: 4rem; height: 4rem; color: var(--success); margin: 0 auto 1rem auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h2 style="color: var(--success);">Installation Complete!</h2>
        <p class="subtitle" style="margin-top: 1rem;">SOF2Panel has been successfully installed.</p>
    </div>
    
    <form method="POST" action="{{ route('install.complete') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Finish & Go to Login</button>
    </form>
@endsection
