@extends('layouts.app')

@section('title', 'Manage Server: ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">{{ $server->name }}</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->game->name }} &bull; {{ $server->host->hostname }}:{{ $server->port }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($status !== 'Running')
                @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->start_server))
                <form action="{{ route('servers.start', $server) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: var(--success); width: auto;">
                        <i data-feather="play" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Start
                    </button>
                </form>
                @endif
            @else
                @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->stop_server))
                <form action="{{ route('servers.stop', $server) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: var(--danger); width: auto;">
                        <i data-feather="square" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Stop
                    </button>
                </form>
                @endif
            @endif
            
            @if(auth()->user()->isAdmin())
            <form action="{{ route('servers.destroy', $server) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="border: 1px solid var(--danger); color: var(--danger); background: var(--bg-card); width: auto;">
                    <i data-feather="trash-2" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Delete
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="stat-grid">
        <div class="glass-panel" style="width: 100%; max-width: none; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
            <div>
                <h3>Status</h3>
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                    <div class="stat-icon {{ $status === 'Running' ? 'text-success' : 'text-danger' }}" style="background: transparent;">
                        <i data-feather="activity" style="width: 32px; height: 32px; color: {{ $status === 'Running' ? 'var(--success)' : 'var(--danger)' }}"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0;">{{ $status }}</h4>
                    </div>
                </div>
            </div>

            @if($status === 'Running')
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <h3 style="margin: 0;">Live Players</h3>
                    <h4 style="margin: 0;" id="playerCountText">Loading...</h4>
                </div>
                <div style="margin-top: 1.25rem; background: var(--bg-dark); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border);">
                    <div id="playerProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); transition: width 0.5s ease;"></div>
                </div>
                <p id="playerCountError" style="color: var(--danger); font-size: 0.85rem; margin-top: 0.5rem; display: none;"></p>
            </div>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div class="glass-panel" style="width: 100%; max-width: none;">
                <h3 style="margin: 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">Connection Info</h3>
                <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">IP Address</span>
                        <span style="font-family: monospace; font-size: 1.1rem; user-select: all;">{{ $server->host->hostname }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Game Port</span>
                        <span style="font-family: monospace; font-size: 1.1rem; color: var(--primary); user-select: all;">{{ $server->port }}</span>
                    </div>
                    @if($server->port_gold)
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Gold Port</span>
                        <span style="font-family: monospace; font-size: 1.1rem; color: var(--warning); user-select: all;">{{ $server->port_gold }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="glass-panel" style="width: 100%; max-width: none;">
                <h3 style="margin: 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">Credentials</h3>
                <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->view_ftp_credentials))
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div id="ftpCreds" style="display: flex; padding: 0.75rem 1rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 6px; font-family: monospace; flex: 1; align-items: center; justify-content: space-between;">
                            <span><i data-feather="lock" style="width: 14px; height: 14px; margin-right: 0.5rem; color: var(--text-muted);"></i> FTP User: {{ $server->ftp_username }} <span style="color:var(--text-muted)">|</span> Pass: <span style="user-select: all; color: var(--success);">{{ \Illuminate\Support\Facades\Crypt::decryptString($server->ftp_password) }}</span></span>
                            <button class="btn" style="width: auto; background: transparent; color: var(--text-muted); padding: 0; border: none; margin-left: 0.5rem;" onclick="copyToClipboard('{{ \Illuminate\Support\Facades\Crypt::decryptString($server->ftp_password) }}')" title="Copy Password"><i data-feather="copy" style="width: 16px; height: 16px;"></i></button>
                        </div>
                        @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->manage_server_users))
                        <button class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); width: auto;" onclick="document.getElementById('changeFtpPassModal').style.display='flex'"><i data-feather="edit-2" style="width: 16px; height: 16px;"></i></button>
                        @endif
                    </div>
                    @endif
                    
                    @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->view_rcon_password))
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div id="rconCreds" style="display: flex; padding: 0.75rem 1rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 6px; font-family: monospace; flex: 1; align-items: center; justify-content: space-between;">
                            <span><i data-feather="key" style="width: 14px; height: 14px; margin-right: 0.5rem; color: var(--text-muted);"></i> RCON Pass: <span style="user-select: all; color: var(--success);">{{ \Illuminate\Support\Facades\Crypt::decryptString($server->rcon_password) }}</span></span>
                            <button class="btn" style="width: auto; background: transparent; color: var(--text-muted); padding: 0; border: none; margin-left: 0.5rem;" onclick="copyToClipboard('{{ \Illuminate\Support\Facades\Crypt::decryptString($server->rcon_password) }}')" title="Copy Password"><i data-feather="copy" style="width: 16px; height: 16px;"></i></button>
                        </div>
                        @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->manage_server_users))
                        <button class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); width: auto;" onclick="document.getElementById('changeRconPassModal').style.display='flex'"><i data-feather="edit-2" style="width: 16px; height: 16px;"></i></button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

    <div class="glass-panel" style="width: 100%; max-width: none; margin-top: 1.5rem;">
        <h3 style="margin: 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 1rem;">Quick Actions</h3>
        
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('servers.players', $server) }}" class="btn btn-primary" style="width: auto; background: var(--success); padding: 0.5rem 1rem;">
                <i data-feather="users" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Live Players
            </a>

            @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->manage_server_users))
            <a href="{{ route('servers.users.index', $server) }}" class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; background: var(--primary);">
                <i data-feather="shield" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Manage Users
            </a>
            @endif
            
            @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->use_web_rcon))
            <a href="{{ route('servers.rcon', $server) }}" class="btn btn-primary" style="width: auto; background: var(--warning); color: #111; padding: 0.5rem 1rem; border: none;">
                <i data-feather="terminal" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Web RCON
            </a>
            @endif
            
            @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->use_ftp))
            <a href="{{ route('servers.ftp', $server) }}" class="btn btn-primary" style="width: auto; background: var(--info); color: #fff; padding: 0.5rem 1rem; border: none;">
                <i data-feather="folder" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Web FTP
            </a>
            @endif
            
            @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->manage_settings))
            <a href="{{ route('servers.backups', $server) }}" class="btn btn-primary" style="width: auto; background: #8b5cf6; color: #fff; padding: 0.5rem 1rem; border: none;">
                <i data-feather="hard-drive" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Backups
            </a>
            @endif
            
            @if(auth()->user()->isAdmin() || (auth()->user()->servers()->where('server_id', $server->id)->first()?->pivot->manage_settings))
            <a href="{{ route('servers.settings', $server) }}" class="btn btn-primary" style="width: auto; background: var(--secondary); color: #fff; padding: 0.5rem 1rem; border: none;">
                <i data-feather="settings" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Settings
            </a>
            @endif
        </div>
    </div>

    <!-- Change FTP Pass Modal -->
    <div id="changeFtpPassModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 400px;">
            <h3 style="margin-bottom: 1.5rem;">Change FTP Password</h3>
            <form method="POST" action="{{ route('servers.password.ftp', $server) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="password" id="ftpPasswordInput" class="form-input" required minlength="6">
                        <button type="button" class="btn" style="width: auto; background: var(--input-bg); border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('ftpPasswordInput').value = Math.random().toString(36).slice(-12) + Math.random().toString(36).slice(-4)">Generate</button>
                    </div>
                </div>
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg);" onclick="document.getElementById('changeFtpPassModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change RCON Pass Modal -->
    <div id="changeRconPassModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 400px;">
            <h3 style="margin-bottom: 1.5rem;">Change RCON Password</h3>
            <form method="POST" action="{{ route('servers.password.rcon', $server) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="rcon_password" id="rconPasswordInput" class="form-input" required minlength="3">
                        <button type="button" class="btn" style="width: auto; background: var(--input-bg); border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('rconPasswordInput').value = Math.random().toString(36).slice(-12)">Generate</button>
                    </div>
                </div>
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg);" onclick="document.getElementById('changeRconPassModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    @if($status === 'Running')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serverId = {{ $server->id }};
            const maxClients = {{ $server->max_clients }};
            const listUrl = `/servers/${serverId}/players/list`;
            const countText = document.getElementById('playerCountText');
            const progressBar = document.getElementById('playerProgressBar');
            const errorText = document.getElementById('playerCountError');
            
            function fetchPlayerCount() {
                fetch(listUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            errorText.style.display = 'none';
                            const count = data.players.length;
                            countText.textContent = `${count} / ${maxClients}`;
                            let pct = (count / maxClients) * 100;
                            if (pct > 100) pct = 100;
                            progressBar.style.width = `${pct}%`;
                            
                            if (pct >= 100) {
                                progressBar.style.background = 'var(--danger)';
                            } else if (pct >= 75) {
                                progressBar.style.background = 'var(--warning)';
                            } else {
                                progressBar.style.background = 'linear-gradient(90deg, var(--primary), var(--secondary))';
                            }
                        } else {
                            throw new Error(data.error || 'Failed to fetch players.');
                        }
                    })
                    .catch(error => {
                        countText.textContent = 'Error';
                        errorText.textContent = 'Could not fetch live player count.';
                        errorText.style.display = 'block';
                    });
            }
            
            fetchPlayerCount();
            setInterval(fetchPlayerCount, 10000); // 10 seconds refresh
        });
    </script>
    @endif

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                Toastify({
                    text: "Password copied to clipboard!",
                    duration: 3000,
                    gravity: "bottom",
                    position: "right",
                    style: {
                        background: "var(--success)",
                        color: "white",
                        borderRadius: "8px",
                        boxShadow: "0 4px 12px rgba(0,0,0,0.15)"
                    }
                }).showToast();
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
@endsection
