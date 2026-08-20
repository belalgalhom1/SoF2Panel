@extends('layouts.app')

@section('title', 'Live Players: ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Live Players</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->name }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('servers.show', $server) }}" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); width: auto;">Back to Server</a>
        </div>
    </div>

    <div class="glass-panel wide">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;">Connected Players</h3>
            <span id="playerCount" class="badge badge-success" style="font-size: 0.9rem;">Loading...</span>
        </div>

        <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>

        <div class="table-responsive">
            <table class="panel-table" id="playersTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Name</th>
                        <th style="width: 100px;">Score</th>
                        <th style="width: 100px;">Ping</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="playersBody">
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Fetching live players...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serverId = {{ $server->id }};
            const listUrl = `/servers/${serverId}/players/list`;
            const tbody = document.getElementById('playersBody');
            const playerCount = document.getElementById('playerCount');
            const errorContainer = document.getElementById('errorContainer');
            
            function fetchPlayers() {
                fetch(listUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            errorContainer.style.display = 'none';
                            renderPlayers(data.players);
                        } else {
                            showError(data.error || 'Failed to fetch players.');
                        }
                    })
                    .catch(error => {
                        showError('Network error while connecting to server.');
                    });
            }
            
            function renderPlayers(players) {
                playerCount.textContent = players.length + ' Online';
                
                if (players.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No players are currently connected.</td></tr>`;
                    return;
                }
                
                let html = '';
                players.forEach(p => {
                    html += `
                        <tr>
                            <td><span style="color: var(--text-muted);">#${p.num}</span></td>
                            <td style="font-weight: 600;">${escapeHtml(p.name)}</td>
                            <td>${p.score}</td>
                            <td><span style="color: ${p.ping > 100 ? 'var(--danger)' : 'var(--success)'};">${p.ping}ms</span></td>
                            <td style="font-family: monospace; color: var(--text-muted);">${escapeHtml(p.address)}</td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            }
            
            function showError(msg) {
                errorContainer.textContent = msg;
                errorContainer.style.display = 'block';
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--danger); padding: 2rem;">Could not load player list.</td></tr>`;
                playerCount.textContent = 'Error';
            }
            
            function escapeHtml(unsafe) {
                return (unsafe || '').toString()
                     .replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
            }
            
            // Fetch immediately
            fetchPlayers();
            
            // Poll every 5 seconds
            setInterval(fetchPlayers, 5000);
        });
    </script>
@endsection
