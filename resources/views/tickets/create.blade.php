@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('tickets.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.875rem;">&larr; Back to Tickets</a>
    <h2 style="margin: 0.5rem 0 0.25rem;">New Support Ticket</h2>
    <p class="subtitle" style="margin: 0;">Tell us what's wrong so we can help you fix it.</p>
</div>

<div class="glass-panel" style="max-width: none;">
    <form action="{{ route('tickets.store') }}" method="POST">
        @csrf
        
        <h3 style="margin-bottom: 1rem; color: var(--primary);">1. What do you need help with?</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            
            <label class="category-card" style="cursor: pointer;">
                <input type="radio" name="category" value="Game Server Issue" style="display: none;" required>
                <div class="card-content" style="padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; text-align: center; transition: all 0.2s;">
                    <i data-feather="server" style="width: 32px; height: 32px; margin-bottom: 1rem; color: var(--text-muted);"></i>
                    <h4 style="margin: 0;">Game Server Issue</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Crashes, configurations, or general server questions.</p>
                </div>
            </label>

            <label class="category-card" style="cursor: pointer;">
                <input type="radio" name="category" value="FTP / Files Issue" style="display: none;" required>
                <div class="card-content" style="padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; text-align: center; transition: all 0.2s;">
                    <i data-feather="folder" style="width: 32px; height: 32px; margin-bottom: 1rem; color: var(--text-muted);"></i>
                    <h4 style="margin: 0;">FTP / Files</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Can't connect to FTP or upload files.</p>
                </div>
            </label>

            <label class="category-card" style="cursor: pointer;">
                <input type="radio" name="category" value="General Question" style="display: none;" required>
                <div class="card-content" style="padding: 1.5rem; border: 2px solid var(--border); border-radius: 12px; text-align: center; transition: all 0.2s;">
                    <i data-feather="help-circle" style="width: 32px; height: 32px; margin-bottom: 1rem; color: var(--text-muted);"></i>
                    <h4 style="margin: 0;">General Question</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Anything else you need to ask.</p>
                </div>
            </label>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--primary);">2. Which server is this about?</h3>
        <div class="form-group" style="margin-bottom: 2rem;">
            <select name="server_id" class="form-input">
                <option value="">None / Not specific to a server</option>
                @foreach($servers as $server)
                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                @endforeach
            </select>
        </div>

        <h3 style="margin-bottom: 1rem; color: var(--primary);">3. Details</h3>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" placeholder="Briefly describe the issue..." required>
        </div>

        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-input" rows="6" placeholder="Please provide as much detail as possible..." required></textarea>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1.1rem;">
                Submit Ticket
            </button>
        </div>
    </form>
</div>

<style>
    .category-card input:checked + .card-content {
        border-color: var(--primary) !important;
        background: rgba(99, 102, 241, 0.1) !important;
    }
    .category-card input:checked + .card-content i {
        color: var(--primary) !important;
    }
    .category-card:hover .card-content {
        border-color: rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.02);
    }
</style>
@endsection
