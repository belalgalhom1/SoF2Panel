@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Manage Users</h2>
        <div style="display: flex; gap: 1rem;">
            @if(\App\Models\Setting::get('external_auth_enabled', false))
            <button class="btn" style="width: auto; background: rgba(99, 102, 241, 0.1); border: 1px solid var(--primary); color: var(--primary);" onclick="document.getElementById('importUserModal').style.display='flex'">
                <i data-feather="download" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Import External
            </button>
            @endif
            <button class="btn btn-primary" style="width: auto;" onclick="document.getElementById('addUserModal').style.display='flex'">
                <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Add User
            </button>
        </div>
    </div>

<div class="table-container">
    <div class="table-header">
        <h3 style="margin: 0;">Registered Users</h3>
    </div>

    <div class="table-responsive">
        <table class="panel-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php
                            $badgeColor = 'var(--primary)';
                            $badgeBg = 'var(--primary)20';
                            $displayRole = ucfirst($user->role);
                            
                            if (!$user->status) {
                                $badgeColor = 'var(--text-muted)';
                                $badgeBg = 'rgba(255,255,255,0.1)';
                                $displayRole = 'Disabled';
                            } elseif ($user->role === 'admin') {
                                $badgeColor = 'var(--danger)';
                                $badgeBg = 'var(--danger)20';
                            }
                        @endphp
                        <span class="status-badge" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}">
                            {{ $displayRole }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-primary" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.2); padding: 0.5rem; width: auto;" onclick="openEditModal({{ $user->id }}, '{{ $user->username }}', '{{ $user->email }}', '{{ $user->role }}', {{ $user->status ? 'true' : 'false' }}, {{ $user->is_external ? 'true' : 'false' }})" title="Edit User">
                                <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                            </button>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-primary" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.5rem; width: auto;" title="Delete User">
                                        <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>

    <div style="margin-top: 1rem;">
        {{ $users->links('pagination::simple-bootstrap-4') }}
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
    <div class="glass-panel" style="max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 1.5rem;">Add New User</h3>
        
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required minlength="8">
            </div>

            <div class="form-group">
                <select name="role" class="form-input" required>
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input" required>
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Create User</button>
                <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('addUserModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

@if(\App\Models\Setting::get('external_auth_enabled', false))
<div id="importUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
    <div class="glass-panel" style="max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 0.5rem;">Import External User</h3>
        <p class="subtitle" style="margin-top: 0; margin-bottom: 1.5rem; font-size: 0.85rem;">Look up a user in your external database (e.g. XenForo) and grant them access to this panel.</p>
        
        <form method="POST" action="{{ route('users.import') }}">
            @csrf
            
            <div class="form-group" style="position: relative;">
                <label class="form-label">External Username or Email</label>
                <input type="text" name="external_username" id="external_username_input" class="form-input" required autocomplete="off">
                <div id="external_username_results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 0.5rem; max-height: 200px; overflow-y: auto; z-index: 1000; margin-top: 0.25rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Panel Role</label>
                <select name="role" class="form-input" required>
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input" required>
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Import User</button>
                <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('importUserModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Edit User Modal -->
<div id="editUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
    <div class="glass-panel" style="max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 1.5rem;">Edit User</h3>
        
        <form method="POST" action="" id="editUserForm">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" id="edit_username" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-input" required>
            </div>
            
            <div class="form-group" id="edit_password_group">
                <label class="form-label">New Password <small style="color: var(--text-muted);">(Leave blank to keep current)</small></label>
                <input type="password" name="password" id="edit_password" class="form-input" minlength="8">
            </div>

            <div class="form-group" id="edit_role_group">
                <label class="form-label">Role</label>
                <select name="role" id="edit_role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group" id="edit_status_group">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-input">
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <button type="button" class="btn" style="flex: 1; border: 1px solid var(--border); color: var(--text-main); background: transparent;" onclick="document.getElementById('editUserModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, username, email, role, status, isExternal) {
        document.getElementById('editUserForm').action = '/users/' + id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_status').value = status ? '1' : '0';
        
        document.getElementById('edit_username').readOnly = isExternal;
        document.getElementById('edit_email').readOnly = isExternal;
        
        if (isExternal) {
            document.getElementById('edit_password_group').style.display = 'none';
        } else {
            document.getElementById('edit_password_group').style.display = 'block';
        }
        
        if (id == {{ auth()->id() }}) {
            document.getElementById('edit_role_group').style.display = 'none';
            document.getElementById('edit_status_group').style.display = 'none';
        } else {
            document.getElementById('edit_role_group').style.display = 'block';
            document.getElementById('edit_status_group').style.display = 'block';
        }

        document.getElementById('editUserModal').style.display = 'flex';
    }
</script>

@if(\App\Models\Setting::get('external_auth_enabled', false))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('external_username_input');
        const resultsContainer = document.getElementById('external_username_results');
        let timeout = null;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();

            if (query.length < 3) {
                resultsContainer.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                fetch(`/users/search-external?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(users => {
                        resultsContainer.innerHTML = '';
                        
                        if (users.length === 0) {
                            resultsContainer.style.display = 'none';
                            return;
                        }

                        users.forEach(user => {
                            const div = document.createElement('div');
                            div.style.padding = '0.75rem 1rem';
                            div.style.cursor = 'pointer';
                            div.style.borderBottom = '1px solid var(--border)';
                            div.innerHTML = `<strong style="color: var(--text-main);">${user.username}</strong> <br><small style="color: var(--text-muted);">${user.email}</small>`;
                            
                            div.addEventListener('mouseover', () => div.style.background = 'rgba(255,255,255,0.05)');
                            div.addEventListener('mouseout', () => div.style.background = 'transparent');
                            
                            div.addEventListener('click', () => {
                                input.value = user.username;
                                resultsContainer.style.display = 'none';
                            });

                            resultsContainer.appendChild(div);
                        });

                        resultsContainer.style.display = 'block';
                    });
            }, 300);
        });

        // Hide when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== input && e.target !== resultsContainer) {
                resultsContainer.style.display = 'none';
            }
        });
    });
</script>
@endif

@endsection
