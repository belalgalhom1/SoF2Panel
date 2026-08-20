@extends('layouts.app')

@section('title', 'Web FTP - ' . $server->name)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0;">Web FTP</h2>
            <p class="subtitle" style="text-align: left; margin-bottom: 0;">{{ $server->name }} &bull; Path: {{ $path }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-primary" style="width: auto;" onclick="document.getElementById('uploadModal').style.display='flex'">Upload File</button>
            <button class="btn btn-primary" style="width: auto; background: var(--secondary);" onclick="document.getElementById('mkdirModal').style.display='flex'">New Folder</button>
            <a href="{{ route('servers.show', $server) }}" class="btn" style="width: auto; border: 1px solid var(--border); color: var(--text-main);">Back to Server</a>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
        <table class="panel-table">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($path !== '/')
                    @php
                        $parentPath = dirname($path);
                        if ($parentPath === '\\' || $parentPath === '.') $parentPath = '/';
                    @endphp
                    <tr>
                        <td><i data-feather="corner-left-up" style="color: var(--text-muted);"></i></td>
                        <td colspan="4"><a href="{{ route('servers.ftp', ['server' => $server, 'path' => $parentPath]) }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">.. (Up a directory)</a></td>
                    </tr>
                @endif
                
                @foreach($items as $item)
                    <tr>
                        <td>
                            @if($item['type'] === 'dir')
                                <i data-feather="folder" style="color: var(--secondary);"></i>
                            @else
                                <i data-feather="file" style="color: var(--text-muted);"></i>
                            @endif
                        </td>
                        <td style="font-weight: 500;">
                            @if($item['type'] === 'dir')
                                <a href="{{ route('servers.ftp', ['server' => $server, 'path' => rtrim($path, '/') . '/' . $item['name']]) }}" style="color: var(--text-main); text-decoration: none;">{{ $item['name'] }}</a>
                            @else
                                {{ $item['name'] }}
                            @endif
                        </td>
                        <td>{{ $item['type'] === 'dir' ? '-' : round($item['size'] / 1024, 2) . ' KB' }}</td>
                        <td style="color: var(--text-muted);">{{ date('Y-m-d H:i', $item['mtime']) }}</td>
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 0.5rem;">
                            @if($item['type'] === 'file')
                                <a href="{{ route('servers.ftp.download', ['server' => $server, 'file' => rtrim($path, '/') . '/' . $item['name']]) }}" class="btn" style="padding: 0.25rem 0.5rem; color: var(--primary);" title="Download">
                                    <i data-feather="download" style="width: 16px; height: 16px;"></i>
                                </a>
                                @if(in_array(pathinfo($item['name'], PATHINFO_EXTENSION), ['cfg', 'txt', 'ini', 'log', 'json', 'mvchat', 'ent', 'arena', 'wpn', 'sh']))
                                    <button class="btn" style="padding: 0.25rem 0.5rem; color: var(--success);" title="Edit" onclick="editFile('{{ rtrim($path, '/') . '/' . $item['name'] }}')">
                                        <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                                    </button>
                                @endif
                            @endif
                            <button class="btn" style="padding: 0.25rem 0.5rem; color: var(--secondary);" title="Rename" onclick="renameItem('{{ rtrim($path, '/') . '/' . $item['name'] }}', '{{ $item['name'] }}')">
                                <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                            </button>
                            <form action="{{ route('servers.ftp.delete', $server) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this?')">
                                @csrf
                                <input type="hidden" name="target" value="{{ rtrim($path, '/') . '/' . $item['name'] }}">
                                <button type="submit" class="btn" style="padding: 0.25rem 0.5rem; color: var(--danger);" title="Delete">
                                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if(count($items) === 0)
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Directory is empty.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 400px;">
            <h3>Upload File</h3>
            <form action="{{ route('servers.ftp.upload', $server) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">
                <div class="form-group" style="margin-top: 1rem;">
                    <input type="file" name="file" class="form-input" required>
                </div>
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Upload</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('uploadModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mkdir Modal -->
    <div id="mkdirModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 400px;">
            <h3>New Folder</h3>
            <form action="{{ route('servers.ftp.mkdir', $server) }}" method="POST">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Folder Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Create</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('mkdirModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rename Modal -->
    <div id="renameModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel" style="width: 400px;">
            <h3>Rename</h3>
            <form action="{{ route('servers.ftp.rename', $server) }}" method="POST">
                @csrf
                <input type="hidden" name="old" id="renameOldInput" value="">
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">New Name</label>
                    <input type="text" name="new" id="renameNewInput" class="form-input" required>
                </div>
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: auto;">Rename</button>
                    <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('renameModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit File Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; backdrop-filter: var(--glass-blur);">
        <div class="glass-panel wide" style="max-height: 90vh; display: flex; flex-direction: column;">
            <h3 id="editTitle">Edit File</h3>
            <div id="editLoading" style="display: none; margin: 2rem 0; text-align: center;">Loading file contents...</div>
            <textarea id="editContent" class="form-input" style="flex: 1; min-height: 500px; font-family: monospace; display: none; white-space: pre;"></textarea>
            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="button" class="btn btn-primary" style="width: auto;" id="editSaveBtn" style="display: none;" onclick="saveFile()">Save Changes</button>
                <button type="button" class="btn" style="border: 1px solid var(--border); color: var(--text-main);" onclick="document.getElementById('editModal').style.display='none'">Close</button>
            </div>
            <input type="hidden" id="editPath" value="">
        </div>
    </div>

    <script>
        function renameItem(oldPath, currentName) {
            document.getElementById('renameOldInput').value = oldPath;
            document.getElementById('renameNewInput').value = currentName;
            document.getElementById('renameModal').style.display = 'flex';
        }

        async function editFile(path) {
            document.getElementById('editPath').value = path;
            document.getElementById('editTitle').innerText = 'Edit File: ' + path.split('/').pop();
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('editContent').style.display = 'none';
            document.getElementById('editSaveBtn').style.display = 'none';
            document.getElementById('editLoading').style.display = 'block';

            try {
                let res = await fetch("{{ route('servers.ftp.edit', $server) }}?file=" + encodeURIComponent(path));
                let data = await res.json();
                document.getElementById('editContent').value = data.content;
                document.getElementById('editLoading').style.display = 'none';
                document.getElementById('editContent').style.display = 'block';
                document.getElementById('editSaveBtn').style.display = 'block';
            } catch (e) {
                showToast('Error loading file.', 'error');
                document.getElementById('editModal').style.display = 'none';
            }
        }

        async function saveFile() {
            let path = document.getElementById('editPath').value;
            let content = document.getElementById('editContent').value;
            let btn = document.getElementById('editSaveBtn');
            btn.innerText = 'Saving...';
            btn.disabled = true;

            try {
                let res = await fetch("{{ route('servers.ftp.update', $server) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ file: path, content: content })
                });
                
                if (res.ok) {
                    showToast('Saved successfully!');
                    document.getElementById('editModal').style.display = 'none';
                } else {
                    showToast('Failed to save file.', 'error');
                }
            } catch (e) {
                showToast('Error saving file.', 'error');
            }

            btn.innerText = 'Save Changes';
            btn.disabled = false;
        }
    </script>
@endsection
