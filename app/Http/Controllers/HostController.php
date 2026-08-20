<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Host;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Crypt;

class HostController extends Controller
{
    public function index()
    {
        $hosts = Host::all();
        return view('hosts.index', compact('hosts'));
    }

    public function create()
    {
        return view('hosts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        try {
            $ssh = new SSH2($request->hostname, $request->port, 5);
            if (!$ssh->login($request->username, $request->password)) {
                throw new \Exception("SSH Authentication Failed");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to host: ' . $e->getMessage())->withInput();
        }

        Host::create([
            'name' => $request->name,
            'hostname' => $request->hostname,
            'port' => $request->port,
            'username' => $request->username,
            'password' => Crypt::encryptString($request->password),
        ]);

        return redirect()->route('hosts.index')->with('success', 'Host added successfully.');
    }

    public function update(Request $request, Host $host)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
        ]);

        $passwordToUse = $request->password;
        $passwordToSave = $host->password;
        
        if ($passwordToUse) {
            $passwordToSave = Crypt::encryptString($passwordToUse);
        } else {
            try {
                $passwordToUse = Crypt::decryptString($host->password);
            } catch (\Exception $e) {
                $passwordToUse = $host->password;
            }
        }

        try {
            $ssh = new SSH2($request->hostname, $request->port, 5);
            if (!$ssh->login($request->username, $passwordToUse)) {
                throw new \Exception("SSH Authentication Failed");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to host: ' . $e->getMessage())->withInput();
        }

        $host->update([
            'name' => $request->name,
            'hostname' => $request->hostname,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $passwordToSave,
        ]);

        return redirect()->route('hosts.index')->with('success', 'Host updated successfully.');
    }

    public function destroy(Host $host)
    {
        if ($host->servers()->count() > 0) {
            return redirect()->route('hosts.index')->with('error', 'Cannot delete host: there are servers currently assigned to it.');
        }

        $host->delete();
        return redirect()->route('hosts.index')->with('success', 'Host deleted successfully.');
    }
}
