@extends('layouts.app')

@section('title', 'Host Servers')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Manage Hosts</h2>
        <a href="{{ route('hosts.create') }}" class="btn btn-primary" style="width: auto;">
            <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Add Host
        </a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3>Registered Hosts</h3>
        </div>
        <div class="table-responsive">
        <table class="panel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Hostname / IP</th>
                    <th>Port</th>
                    <th>Username</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hosts as $host)
                    <tr>
                        <td>{{ $host->id }}</td>
                        <td style="font-weight: 600;">{{ $host->name }}</td>
                        <td>{{ $host->hostname }}</td>
                        <td>{{ $host->port }}</td>
                        <td>{{ $host->username }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-primary" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.2); padding: 0.5rem; width: auto;" onclick="openEditModal({{ $host->id }}, '{{ addslashes($host->name) }}', '{{ addslashes($host->hostname) }}', '{{ $host->port }}', '{{ addslashes($host->username) }}')" title="Edit Host">
                                    <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                </button>
                                <form action="{{ route('hosts.destroy', $host) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-primary" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.5rem; width: auto;" onclick="return confirm('Delete this host? This will break associated servers.')" title="Delete Host">
                                        <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hosts registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Edit Host Modal -->
    <div id="editHostModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 500px; max-width: 90%; max-height: 90vh; overflow-y: auto;">
            <h3 style="margin-bottom: 1.5rem; margin-top: 0;">Edit Host</h3>
            
            <form id="editHostForm" method="POST" action="">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Hostname / IP</label>
                    <input type="text" name="hostname" id="edit_hostname" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">SSH Port</label>
                    <input type="number" name="port" id="edit_port" class="form-input" required min="1" max="65535">
                </div>

                <div class="form-group">
                    <label class="form-label">SSH Username</label>
                    <input type="text" name="username" id="edit_username" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">SSH Password <small style="color: var(--text-muted);">(Leave blank to keep existing)</small></label>
                    <input type="password" name="password" id="edit_password" class="form-input">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('editHostModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(id, name, hostname, port, username) {
        var form = document.getElementById('editHostForm');
        form.action = '/hosts/' + id;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_hostname').value = hostname;
        document.getElementById('edit_port').value = port;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_password').value = '';
        
        document.getElementById('editHostModal').style.display = 'flex';
    }
    </script>
@endsection
