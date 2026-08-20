<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalAuthService
{
    /**
     * Attempt to authenticate a user against the external database.
     * 
     * @param string $login Username or Email
     * @param string $password Plaintext password
     * @return object|null Returns the external user object if successful, null otherwise.
     */
    public function attempt($login, $password)
    {
        try {
            $enabled = \App\Models\Setting::get('external_auth_enabled', false);
            if (!$enabled) {
                return null;
            }

            $this->configureExternalDatabase();

            $type = \App\Models\Setting::get('external_auth_type', 'XenForo');
            
            if ($type === 'XenForo') {
                return $this->attemptXenForo($login, $password);
            }

            return $this->attemptGeneric($login, $password);

        } catch (\Exception $e) {
            Log::error('External Auth Error: ' . $e->getMessage());
            return null;
        }
    }

    private function attemptGeneric($login, $password)
    {
        $db = DB::connection('external');
        $table = \App\Models\Setting::get('external_auth_table', 'users');
        $colUsername = \App\Models\Setting::get('external_auth_col_username', 'username');
        $colEmail = \App\Models\Setting::get('external_auth_col_email', 'email');
        $colPassword = \App\Models\Setting::get('external_auth_col_password', 'password');
        
        $user = $db->table($table)
            ->where($colUsername, $login)
            ->orWhere($colEmail, $login)
            ->first();

        if ($user) {
            $hash = $user->{$colPassword};
            if (password_verify($password, $hash)) {
                return (object) [
                    'username' => $user->{$colUsername},
                    'email' => $user->{$colEmail}
                ];
            }
        }

        return null;
    }

    private function attemptXenForo($login, $password)
    {
        $db = DB::connection('external');
        
        $table = \App\Models\Setting::get('external_auth_table', 'xf_user');
        $colUsername = \App\Models\Setting::get('external_auth_col_username', 'username');
        $colEmail = \App\Models\Setting::get('external_auth_col_email', 'email');
        $colId = \App\Models\Setting::get('external_auth_col_id', 'user_id');

        $user = $db->table($table)
            ->where($colUsername, $login)
            ->orWhere($colEmail, $login)
            ->first();

        if ($user) {
            $auth = $db->table('xf_user_authenticate')
                ->where('user_id', $user->{$colId})
                ->first();

            if ($auth && $auth->data) {
                $data = @unserialize($auth->data);
                
                if (is_array($data) && isset($data['hash'])) {
                    if (password_verify($password, $data['hash'])) {
                        return (object) [
                            'username' => $user->{$colUsername},
                            'email' => $user->{$colEmail}
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Look up a user in the external database without authenticating.
     * 
     * @param string $login Username or Email
     * @return object|null Returns the external user object if found, null otherwise.
     */
    public function lookup($login)
    {
        try {
            $enabled = \App\Models\Setting::get('external_auth_enabled', false);
            if (!$enabled) {
                return null;
            }

            $this->configureExternalDatabase();
            $db = DB::connection('external');

            $type = \App\Models\Setting::get('external_auth_type', 'XenForo');
            $table = \App\Models\Setting::get('external_auth_table', $type === 'XenForo' ? 'xf_user' : 'users');
            $colUsername = \App\Models\Setting::get('external_auth_col_username', 'username');
            $colEmail = \App\Models\Setting::get('external_auth_col_email', 'email');

            $user = $db->table($table)
                ->where($colUsername, $login)
                ->orWhere($colEmail, $login)
                ->first();

            if ($user) {
                return (object) [
                    'username' => $user->{$colUsername},
                    'email' => $user->{$colEmail}
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('External Auth Lookup Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for users in the external database.
     * 
     * @param string $query The search query
     * @return array Returns an array of matching user objects
     */
    public function search($query)
    {
        try {
            $enabled = \App\Models\Setting::get('external_auth_enabled', false);
            if (!$enabled || strlen($query) < 3) {
                return [];
            }

            $this->configureExternalDatabase();
            $db = DB::connection('external');

            $type = \App\Models\Setting::get('external_auth_type', 'XenForo');
            $table = \App\Models\Setting::get('external_auth_table', $type === 'XenForo' ? 'xf_user' : 'users');
            $colUsername = \App\Models\Setting::get('external_auth_col_username', 'username');
            $colEmail = \App\Models\Setting::get('external_auth_col_email', 'email');

            $users = $db->table($table)
                ->where($colUsername, 'LIKE', "%{$query}%")
                ->orWhere($colEmail, 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();

            return $users->map(function ($user) use ($colUsername, $colEmail) {
                return (object) [
                    'username' => $user->{$colUsername},
                    'email' => $user->{$colEmail}
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('External Auth Search Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Configure the external database connection dynamically.
     */
    private function configureExternalDatabase()
    {
        config(['database.connections.external' => [
            'driver' => 'mysql',
            'host' => \App\Models\Setting::get('external_auth_host', '127.0.0.1'),
            'port' => \App\Models\Setting::get('external_auth_port', '3306'),
            'database' => \App\Models\Setting::get('external_auth_database', 'xenforo'),
            'username' => \App\Models\Setting::get('external_auth_username', 'root'),
            'password' => \App\Models\Setting::get('external_auth_password', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]]);

        DB::purge('external');
    }
}
