<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallController extends Controller
{
    public function step1()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        return view('install.step1');
    }

    public function processStep1(Request $request)
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_database' => 'required',
            'db_username' => 'required',
        ]);

        try {
            $originalDefault = config('database.default');
            
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);
            
            DB::purge('mysql');
            DB::connection('mysql')->getPdo();

            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                copy(base_path('.env.example'), $envPath);
            }
            
            $envContent = file_get_contents($envPath);
            
            $newAppKey = 'base64:' . base64_encode(random_bytes(32));
            config(['app.key' => $newAppKey]);

            $updates = [
                'APP_KEY' => $newAppKey,
                'APP_DEBUG' => 'false',
                'APP_ENV' => 'production',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ];

            foreach ($updates as $key => $value) {
                $escapedValue = '"' . str_replace('"', '\"', $value) . '"';
                
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$escapedValue}";
                }
            }
            
            file_put_contents($envPath, $envContent);

            Artisan::call('migrate:fresh', ['--force' => true]);

            return redirect()->route('install.step2');

        } catch (\Exception $e) {
            config(['database.default' => $originalDefault ?? 'sqlite']);
            return back()->with('error', 'Database connection failed: ' . $e->getMessage())->withInput();
        }
    }

    public function step2()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        return view('install.step2');
    }

    public function processStep2(Request $request)
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('install.complete');
    }

    public function complete()
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        return view('install.complete');
    }

    public function processComplete(Request $request)
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }
        file_put_contents(storage_path('installed'), 'SOF2Panel Installed on ' . date('Y-m-d H:i:s'));
        return redirect('/login');
    }
}
