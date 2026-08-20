<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use phpseclib3\Net\SFTP;
use Illuminate\Support\Facades\Crypt;

class WebFtpController extends Controller
{
    protected function getSFTP(Server $server)
    {
        $host = $server->host;
        $sftp = new SFTP($host->hostname, $host->port);
        if (!$sftp->login($server->ftp_username, Crypt::decryptString($server->ftp_password))) {
            throw new \Exception("SFTP Authentication Failed");
        }
        return $sftp;
    }

    protected function checkPermission(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->use_ftp) {
                abort(403);
            }
        }
    }

    public function index(Request $request, Server $server)
    {
        $this->checkPermission($server);

        $path = $request->query('path', '/');
        if (strpos($path, '..') !== false) {
            $path = '/';
        }

        $sftp = $this->getSFTP($server);
        
        $baseDir = "/home/{$server->ftp_username}";
        $currentDir = rtrim($baseDir . $path, '/');
        if (empty($currentDir)) $currentDir = $baseDir;

        $rawFiles = $sftp->rawlist($currentDir);
        if ($rawFiles === false) {
            return back()->with('error', 'Directory does not exist or permission denied.');
        }

        $files = [];
        $folders = [];

        foreach ($rawFiles as $name => $stat) {
            if ($name === '..' && $path === '/') continue;
            if (str_starts_with($name, '.')) continue;

            $item = [
                'name' => $name,
                'type' => $stat['type'] == 2 ? 'dir' : 'file',
                'size' => $stat['size'],
                'mtime' => $stat['mtime'],
            ];

            if ($item['type'] === 'dir') {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }

        usort($folders, fn($a, $b) => strcmp($a['name'], $b['name']));
        usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
        
        $items = array_merge($folders, $files);

        return view('servers.ftp', compact('server', 'items', 'path'));
    }

    public function upload(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $request->validate(['file' => 'required|file', 'path' => 'required|string']);

        $path = $request->path;
        if (strpos($path, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = rtrim($baseDir . $path, '/') . '/' . $request->file('file')->getClientOriginalName();

        $sftp->put($target, file_get_contents($request->file('file')->getRealPath()));

        return back()->with('success', 'File uploaded successfully.');
    }

    public function download(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $path = $request->query('file');
        if (!$path || strpos($path, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = $baseDir . $path;

        $content = $sftp->get($target);
        if ($content === false) abort(404);

        return response($content)->header('Content-Type', 'application/octet-stream')
                                ->header('Content-Disposition', 'attachment; filename="'.basename($target).'"');
    }

    public function delete(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $request->validate(['target' => 'required|string']);
        $path = $request->target;
        if (strpos($path, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = rtrim($baseDir, '/') . $path;

        $host = $server->host;
        $ssh = new \phpseclib3\Net\SSH2($host->hostname, $host->port);
        $ssh->login($server->ftp_username, Crypt::decryptString($server->ftp_password));
        $ssh->exec("rm -rf " . escapeshellarg($target));

        return back()->with('success', 'Deleted successfully.');
    }

    public function mkdir(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $request->validate(['path' => 'required|string', 'name' => 'required|string']);
        $path = $request->path;
        if (strpos($path, '..') !== false || strpos($request->name, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = rtrim($baseDir . $path, '/') . '/' . $request->name;

        $sftp->mkdir($target);

        return back()->with('success', 'Folder created successfully.');
    }

    public function rename(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $request->validate(['old' => 'required|string', 'new' => 'required|string']);
        if (strpos($request->old, '..') !== false || strpos($request->new, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $oldPath = rtrim($baseDir, '/') . $request->old;
        $newPath = rtrim($baseDir, '/') . dirname($request->old) . '/' . $request->new;

        $sftp->rename($oldPath, $newPath);

        return back()->with('success', 'Renamed successfully.');
    }

    public function edit(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $path = $request->query('file');
        if (!$path || strpos($path, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = $baseDir . $path;

        $content = $sftp->get($target);
        if ($content === false) abort(404);

        return response()->json(['content' => $content]);
    }

    public function update(Request $request, Server $server)
    {
        $this->checkPermission($server);
        $request->validate(['file' => 'required|string', 'content' => 'required|string']);
        $path = $request->file;
        if (strpos($path, '..') !== false) abort(403);

        $sftp = $this->getSFTP($server);
        $baseDir = "/home/{$server->ftp_username}";
        $target = $baseDir . $path;

        $sftp->put($target, $request->content);

        return response()->json(['success' => true]);
    }
}
