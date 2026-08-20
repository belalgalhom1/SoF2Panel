@extends('layouts.app')

@section('title', 'Web RCON - ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Web RCON Console</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->name }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('servers.show', $server) }}" class="btn" style="border: 1px solid var(--border); color: var(--text-main);">Back to Server</a>
        </div>
    </div>

    <div class="glass-panel wide" style="display: flex; flex-direction: column; height: 60vh;">
        <div id="terminal" style="flex: 1; background: #000; color: #0f0; font-family: monospace; padding: 1rem; overflow-y: auto; border-radius: 0.5rem; margin-bottom: 1rem; white-space: pre-wrap; font-size: 0.875rem;">
            <div>> Welcome to Web RCON for {{ $server->name }}</div>
            <div>> Type a command and press Enter to execute.</div>
        </div>

        <form id="rconForm" style="display: flex; gap: 1rem;" onsubmit="event.preventDefault(); sendCommand();">
            <input type="text" id="cmdInput" class="form-input" placeholder="status, map mp_kamchatka, etc..." required autocomplete="off" style="font-family: monospace;">
            <button type="submit" id="sendBtn" class="btn btn-primary" style="width: 150px;">Send</button>
        </form>
    </div>

    <script>
        const terminal = document.getElementById('terminal');
        const cmdInput = document.getElementById('cmdInput');
        const sendBtn = document.getElementById('sendBtn');
        let history = [];
        let historyIndex = -1;

        function appendLog(text, type = 'info') {
            const div = document.createElement('div');
            div.textContent = text;
            if (type === 'cmd') div.style.color = '#fff';
            if (type === 'error') div.style.color = '#f00';
            terminal.appendChild(div);
            terminal.scrollTop = terminal.scrollHeight;
        }

        async function sendCommand() {
            const cmd = cmdInput.value.trim();
            if (!cmd) return;

            cmdInput.value = '';
            cmdInput.disabled = true;
            sendBtn.disabled = true;

            appendLog(`\n[${new Date().toLocaleTimeString()}] > ${cmd}`, 'cmd');
            history.push(cmd);
            historyIndex = history.length;

            try {
                const res = await fetch("{{ route('servers.rcon.execute', $server) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ command: cmd })
                });
                
                if (res.ok) {
                    const data = await res.json();
                    appendLog(data.response);
                } else {
                    appendLog(`Request failed: ${res.statusText}`, 'error');
                }
            } catch (e) {
                appendLog(`Connection error.`, 'error');
            }

            cmdInput.disabled = false;
            sendBtn.disabled = false;
            cmdInput.focus();
        }

        cmdInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                if (historyIndex > 0) {
                    historyIndex--;
                    cmdInput.value = history[historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                if (historyIndex < history.length - 1) {
                    historyIndex++;
                    cmdInput.value = history[historyIndex];
                } else {
                    historyIndex = history.length;
                    cmdInput.value = '';
                }
            }
        });
    </script>
@endsection
