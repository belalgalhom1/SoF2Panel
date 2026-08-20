@extends('layouts.app')

@section('title', 'Manage Users: ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Manage Users</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->name }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('servers.show', $server) }}" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); width: auto;">Back to Server</a>
            <button class="btn btn-primary" style="width: auto;" onclick="document.getElementById('addUserModal').style.display='flex'">
                <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Add User
            </button>
        </div>
    </div>

    <div class="glass-panel" style="width: 100%; max-width: none;">
        <h3>Assigned Users</h3>
        @if($serverUsers->count() > 0 || $globalAdmins->count() > 0)
            <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th style="text-align: left; padding: 0.75rem; color: var(--text-muted); font-weight: 500;">Username</th>
                        <th style="text-align: left; padding: 0.75rem; color: var(--text-muted); font-weight: 500;">Email</th>
                        <th style="text-align: left; padding: 0.75rem; color: var(--text-muted); font-weight: 500;">Access Level</th>
                        <th style="text-align: right; padding: 0.75rem; color: var(--text-muted); font-weight: 500;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($globalAdmins as $admin)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem 0.75rem;">{{ $admin->username }}</td>
                        <td style="padding: 1rem 0.75rem; color: var(--text-muted);">{{ $admin->email }}</td>
                        <td style="padding: 1rem 0.75rem;">
                            <span class="badge" style="background: var(--primary); color: white;">
                                {{ ucfirst($admin->role) }} (Full Access)
                            </span>
                        </td>
                        <td style="padding: 1rem 0.75rem; text-align: right;">
                            <span style="color: var(--text-muted); font-size: 0.85rem;"><em>Cannot be edited</em></span>
                        </td>
                    </tr>
                    @endforeach

                    @foreach($serverUsers as $u)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem 0.75rem;">{{ $u->username }}</td>
                        <td style="padding: 1rem 0.75rem; color: var(--text-muted);">{{ $u->email }}</td>
                        <td style="padding: 1rem 0.75rem;">
                            <span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-main);">
                                Assigned User
                            </span>
                        </td>
                        <td style="padding: 1rem 0.75rem; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                <button class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: var(--input-bg); padding: 0.25rem 0.75rem;" onclick="document.getElementById('editUserModal-{{ $u->id }}').style.display='flex'">Edit Permissions</button>
                                <form action="{{ route('servers.users.destroy', [$server, $u->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="border: 1px solid var(--danger); color: var(--danger); background: transparent; padding: 0.25rem 0.75rem;" onclick="return confirm('Are you sure you want to remove this user from the server?');">Remove</button>
                                </form>
                            </div>
                        </td>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p style="color: var(--text-muted); margin-top: 1rem;">No users have been assigned to this server yet.</p>
        @endif
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="max-height: 90vh; overflow-y: auto; width: 500px;">
            <h3 style="margin-bottom: 1.5rem;">Add User</h3>
            
            <form method="POST" action="{{ route('servers.users.store', $server) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-input" required>
                        <option value="">Select a user...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_server" value="1" checked> View Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="start_server" value="1"> Start Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="stop_server" value="1"> Stop Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="use_ftp" value="1"> Use FTP</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_ftp_credentials" value="1"> View FTP Credentials</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="use_web_rcon" value="1"> Use Web RCON</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_rcon_password" value="1"> View RCON Password</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_logs" value="1"> View Logs</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="manage_server_users" value="1"> Manage Server Users</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="manage_settings" value="1"> Manage Settings</label>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Add User</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('addUserModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit User Modals -->
    @foreach($serverUsers as $u)
    <div id="editUserModal-{{ $u->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="max-height: 90vh; overflow-y: auto; width: 500px; text-align: left;">
            <h3 style="margin-bottom: 1.5rem;">Edit Permissions: {{ $u->username }}</h3>
            
            <form method="POST" action="{{ route('servers.users.update', [$server, $u->id]) }}">
                @csrf
                @method('PUT')
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_server" value="1" {{ $u->pivot->view_server ? 'checked' : '' }}> View Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="start_server" value="1" {{ $u->pivot->start_server ? 'checked' : '' }}> Start Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="stop_server" value="1" {{ $u->pivot->stop_server ? 'checked' : '' }}> Stop Server</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="use_ftp" value="1" {{ $u->pivot->use_ftp ? 'checked' : '' }}> Use FTP</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_ftp_credentials" value="1" {{ $u->pivot->view_ftp_credentials ? 'checked' : '' }}> View FTP Credentials</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="use_web_rcon" value="1" {{ $u->pivot->use_web_rcon ? 'checked' : '' }}> Use Web RCON</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_rcon_password" value="1" {{ $u->pivot->view_rcon_password ? 'checked' : '' }}> View RCON Password</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="view_logs" value="1" {{ $u->pivot->view_logs ? 'checked' : '' }}> View Logs</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="manage_server_users" value="1" {{ $u->pivot->manage_server_users ? 'checked' : '' }}> Manage Server Users</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;"><input type="checkbox" name="manage_settings" value="1" {{ $u->pivot->manage_settings ? 'checked' : '' }}> Manage Settings</label>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save Changes</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('editUserModal-{{ $u->id }}').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="view_rcon_password"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if(this.checked) {
                        let form = this.closest('form');
                        let webRcon = form.querySelector('input[name="use_web_rcon"]');
                        if(webRcon) webRcon.checked = true;
                    }
                });
            });
            document.querySelectorAll('input[name="use_web_rcon"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if(this.checked) {
                        let form = this.closest('form');
                        let viewRcon = form.querySelector('input[name="view_rcon_password"]');
                        if(viewRcon) viewRcon.checked = true;
                    }
                });
            });
            
            document.querySelectorAll('input[name="view_ftp_credentials"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if(this.checked) {
                        let form = this.closest('form');
                        let useFtp = form.querySelector('input[name="use_ftp"]');
                        if(useFtp) useFtp.checked = true;
                    }
                });
            });
            document.querySelectorAll('input[name="use_ftp"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if(this.checked) {
                        let form = this.closest('form');
                        let viewFtp = form.querySelector('input[name="view_ftp_credentials"]');
                        if(viewFtp) viewFtp.checked = true;
                    }
                });
            });
        });
    </script>
@endsection
