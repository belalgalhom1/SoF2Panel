@extends('layouts.app')

@section('title', 'Global Settings')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Global Settings</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">Manage core panel features and integrations</p>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @foreach ($errors->all() as $error)
                    showToast("{{ addslashes($error) }}", 'error');
                @endforeach
            });
        </script>
    @endif

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; max-width: 900px;">
        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 0.5rem;">General Settings</h3>
            <p class="subtitle" style="margin-top: 0; margin-bottom: 1.5rem; font-size: 0.9rem;">
                Basic panel branding and configuration.
            </p>

            <form action="{{ route('admin.settings.general') }}" method="POST">
                @csrf
                
                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Panel Name</label>
                        <input type="text" class="form-input" name="app_name" value="{{ $general['app_name'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Max Backups (Per Server)</label>
                        <input type="number" class="form-input" name="backup_limit" value="{{ $general['backup_limit'] }}" min="1" max="50" required>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.75rem 2rem;">Save General Settings</button>
                </div>
            </form>
        </div>

        <div class="glass-panel" style="width: 100%; max-width: none;">
            <h3 style="margin-bottom: 0.5rem;">External Authentication</h3>
            <p class="subtitle" style="margin-top: 0; margin-bottom: 1.5rem; font-size: 0.9rem;">
                Configure a bridge to authenticate users against an external database like XenForo or a custom website
            </p>

            <form action="{{ route('admin.settings.external-auth') }}" method="POST">
                @csrf
                
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label" style="display: flex; align-items: center; cursor: pointer;">
                        <input type="hidden" name="external_auth_enabled" value="0">
                        <input type="checkbox" name="external_auth_enabled" value="1" {{ $externalAuth['enabled'] ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="font-weight: 500; font-size: 1.05rem;">Enable External Authentication</span>
                    </label>
                </div>

                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Bridge Type</label>
                        <select name="external_auth_type" class="form-input" style="cursor: pointer;">
                            <option value="XenForo" {{ $externalAuth['type'] === 'XenForo' ? 'selected' : '' }}>XenForo</option>
                            <option value="generic" {{ $externalAuth['type'] === 'generic' ? 'selected' : '' }}>Generic Database</option>
                        </select>
                    </div>
                </div>

                <h4 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Database Connection</h4>
                
                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Host</label>
                        <input type="text" class="form-input" name="external_auth_host" value="{{ $externalAuth['host'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-input" name="external_auth_port" value="{{ $externalAuth['port'] }}" required>
                    </div>
                </div>

                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-input" name="external_auth_database" value="{{ $externalAuth['database'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" name="external_auth_username" value="{{ $externalAuth['username'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-input" name="external_auth_password" placeholder="Leave blank to keep current">
                        <p class="subtitle" style="font-size: 0.75rem; margin-top: 0.5rem;">Only fill if changing.</p>
                    </div>
                </div>

                <h4 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Table Mappings</h4>
                <p class="subtitle" style="margin-bottom: 1.5rem; font-size: 0.85rem;">Define which columns in your external database map to our required fields. For XenForo, the defaults usually work perfectly.</p>
                
                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Table Name</label>
                        <input type="text" class="form-input" name="external_auth_table" value="{{ $externalAuth['table'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">ID Column</label>
                        <input type="text" class="form-input" name="external_auth_col_id" value="{{ $externalAuth['col_id'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Username Column</label>
                        <input type="text" class="form-input" name="external_auth_col_username" value="{{ $externalAuth['col_username'] }}" required>
                    </div>
                </div>

                <div style="display: flex; gap: 2rem;" class="flex-responsive">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Email Column</label>
                        <input type="text" class="form-input" name="external_auth_col_email" value="{{ $externalAuth['col_email'] }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Password Column</label>
                        <input type="text" class="form-input" name="external_auth_col_password" value="{{ $externalAuth['col_password'] }}" required>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">
                        <i data-feather="save" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
