@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin: 0;">API Keys</h2>
        <p class="page-subtitle" style="margin-bottom: 0;">Manage your personal API keys for programmatic access.</p>
    </div>
    <button class="btn btn-primary" style="width: auto;" onclick="document.getElementById('addKeyModal').style.display='flex'">
        <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Generate Key
    </button>
</div>

@if(session('new_token'))
<div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <div style="background: var(--success); color: #000; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i data-feather="check" style="width: 18px; height: 18px;"></i>
        </div>
        <h3 style="color: var(--success); margin: 0; font-size: 1.25rem;">API Key Generated Successfully</h3>
    </div>
    
    <p style="color: var(--text-main); margin: 0;">
        Please copy your new API key below. <strong style="color: var(--warning);">For security reasons, it will never be shown again!</strong>
    </p>
    
    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-darker); padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border);">
        <input type="text" id="newTokenInput" value="{{ session('new_token') }}" readonly style="flex: 1; background: transparent; border: none; color: var(--text-main); font-family: monospace; font-size: 1.1rem; padding: 0.5rem; outline: none;">
        <button type="button" class="btn btn-primary" onclick="copyToken()" id="copyTokenBtn" style="width: auto; padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i data-feather="copy" style="width: 16px; height: 16px;"></i> <span>Copy</span>
        </button>
    </div>
</div>

<script>
function copyToken() {
    var copyText = document.getElementById("newTokenInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    navigator.clipboard.writeText(copyText.value);
    
    var btn = document.getElementById("copyTokenBtn");
    var originalHtml = btn.innerHTML;
    btn.innerHTML = '<i data-feather="check" style="width: 16px; height: 16px;"></i> <span>Copied!</span>';
    btn.style.background = 'var(--success)';
    btn.style.color = '#000';
    feather.replace();
    
    setTimeout(function() {
        btn.innerHTML = originalHtml;
        btn.style.background = '';
        btn.style.color = '';
        feather.replace();
    }, 2000);
}
</script>
@endif

<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- Active Keys -->
    <div class="table-container">
        <div class="table-header">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="key" style="color: var(--primary); width: 20px; height: 20px;"></i> Active Keys
            </h3>
        </div>
        
        @if($keys->isEmpty())
            <div style="padding: 3rem; text-align: center;">
                <i data-feather="shield-off" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-muted); margin: 0;">You have not generated any API keys yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            @if(auth()->user()->isAdmin())
                            <th>User</th>
                            @endif
                            <th>Created</th>
                            <th>Last Used</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keys as $key)
                        <tr>
                            <td><strong>{{ $key->name }}</strong></td>
                            @if(auth()->user()->isAdmin())
                            <td>
                                @if($key->user)
                                    <span>{{ $key->user->username }}</span>
                                @else
                                    <span style="color: var(--text-muted);">Unknown</span>
                                @endif
                            </td>
                            @endif
                            <td style="color: var(--text-muted);">{{ $key->created_at->diffForHumans() }}</td>
                            <td style="color: var(--text-muted);">
                                @if($key->last_used_at)
                                    {{ $key->last_used_at->diffForHumans() }}
                                @else
                                    <em>Never</em>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <form action="{{ route('api-keys.destroy', $key) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this API key? Any applications using it will immediately lose access.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="padding: 0.25rem 0.5rem; background: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); width: auto;">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- API Logs -->
    <div class="table-container">
        <div class="table-header">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="activity" style="color: var(--info); width: 20px; height: 20px;"></i> Recent API Activity
            </h3>
        </div>
        
        @if($logs->isEmpty())
            <div style="padding: 3rem; text-align: center;">
                <p style="color: var(--text-muted); margin: 0;">No API requests have been logged yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Target Server</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td style="color: var(--text-muted);">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if($log->user)
                                    <span>{{ $log->user->username }}</span>
                                @else
                                    <span style="color: var(--text-muted);">Unknown</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge" style="background: rgba(59,130,246,0.1); color: var(--info);">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>{{ $log->target ?? '-' }}</td>
                            <td style="color: var(--text-muted);">{{ $log->ip }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Add Key Modal -->
<div id="addKeyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
    <div class="glass-panel" style="width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 1.5rem; margin-top: 0;">Generate New API Key</h3>
        
        <form method="POST" action="{{ route('api-keys.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Key Name</label>
                <input type="text" name="name" class="form-input" required autofocus>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Generate</button>
                <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('addKeyModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
