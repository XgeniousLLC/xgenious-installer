<?php

namespace Xgenious\Installer\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class InstallationHelper
{
    public static $api_path = 'https://license.xgenious.com/api/install-verify';
    public static function folders(){
       return  [ 'bootstrap/cache/', 'storage/', 'storage/app/', 'storage/framework/', 'storage/logs/'];
    }
    public static function extensions()
    {
        return config('installer.extensions',['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'fileinfo','json']);
    }
    public static function php_version()
    {
        return version_compare(PHP_VERSION, config('installer.php_version'), '>=');
    }
    public static function isInstallerNeeded()
    {
        $return_val = false;
        try {
            $return_val = !EnvHelper::keyExists('DB_CONNECTION');
        } catch (\Exception $e) {
            return false;
        }
        return $return_val;
    }

    public  static function extension_check($name)
    {
        if (!extension_loaded($name)) {
            $response = false;
        } else {
            $response = true;
        }
        return $response;
    }

    public  static function folder_permission($name)
    {
        $path = public_path('../'.$name);
        return self::ensure_directory_exists_and_writable($path);
    }

    public static function extract_path($fullPath)
    {
        if (preg_match('/@(Core|core|Core|core)(.*)/', $fullPath, $matches)) {
            return '@Core' . $matches[2];
        } elseif (preg_match('/(Core|core)(.*)/', $fullPath, $matches)) {
            return 'Core' . $matches[2];
        }
        return null; // Return null if no match found
    }

    private static function ensure_directory_exists_and_writable($path)
    {
        // Check if the directory exists
        if (!is_dir($path)) {
            // Directory does not exist, create it
            if (!mkdir($path, 0755, true)) {
                // Failed to create the directory
                return false;
            }
        }

        // Check if the directory is writable
        if (!is_writable($path)) {
            // Directory is not writable, attempt to set permissions
            if (!chmod($path, 0755)) {
                // Failed to set permissions
                return false;
            }
        }

        // Check the permissions
        $perm = substr(sprintf('%o', fileperms($path)), -4);

        // Ensure the directory has at least 0755 permissions
        return $perm >= '0755';
    }


    public static function has_database_file()
    {
        // check storage has the database file or not
        return Storage::disk('local')->exists('database.sql');
    }
    public static function has_env_sample_file()
    {
        //check env_example_file path
        return !is_dir(config('installer.env_example_path')) && file_exists(config('installer.env_example_path'));
    }


    function verify_input_fields($all_fields)
    {
        $error_count = 0;
        unset($all_fields['database_password']);
        foreach ($all_fields as $key => $value) {
            if (empty($_POST[$key])) {
                $error_list['message'][$key] = $key;
                $error_count++;
            }
        }
        $error_list['error'] = $error_count > 0 ? true : false;
        return $error_list;
    }

    public static function is_multi_tenant()
    {
        return config('installer.multi_tenant',false);
    }


    public static function check_database_connection($db_host,$db_name,$db_user,$db_pass)
    {
        self::set_temp_db_connection($db_host,$db_name,$db_user,$db_pass);
        try {
            DB::connection('temp')->getPdo();
            return ['status' => true,'msg' => 'connection successful'];
        } catch (\Exception $e) {
            return ['status' => false,'msg' => $e->getMessage()];
        }
    }
    private static function set_temp_db_connection($db_host,$db_name,$db_user,$db_pass){
        Config::set('database.connections.temp', [
            'driver' => 'mysql',
            'host' => $db_host,
            'port' => Config::get('database.connections.mysql.port'),
            'database' => $db_name,
            'username' => $db_user,
            'password' => is_null($db_pass) ? "" : $db_pass,
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);

        DB::purge('temp'); // Clear the previous connection, if any
        DB::reconnect('temp'); // Reconnect with the new configuration
    }

    public static function generate_env_file($keyValuePairs)
    {
        $envSamplePath = self::has_env_sample_file() ? \config('installer.env_example_path') : __DIR__.'/../../env-sample.txt'; // Adjust the path as needed

        $envPath = base_path('.env');

        // Read the sample environment file
        $envContent = File::get($envSamplePath);

        // Replace the placeholders with actual values
        $keyValuePairs['APP_KEY'] = (new self())->generate_app_key();
        foreach ($keyValuePairs as $key => $value) {
            $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
        }

        // Write the updated content to the new .env file
        File::put($envPath, $envContent);
    }

    private function generate_app_key()
    {
        return 'base64:' . base64_encode(Str::random(32));
    }

    public  static  function insert_database_sql_file($db_host,$db_name,$db_user,$db_pass)
    {
        $db = new \PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        // set the PDO error mode to exception
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $query = Storage::drive('local')->get("database.sql");
        $stmt = $db->prepare($query);
        $result = (bool)$stmt->execute();

        return [
            'type' => $result ? 'success':'danger',
            'msg' => $result ? 'Database insert success' : 'SQL file not found.'
        ];
    }

    public static  function create_admin($admin_email,$admin_password,$admin_username,$admin_name,$db_host,$db_name,$db_user,$db_pass)
    {
        self::set_temp_db_connection($db_host,$db_name,$db_user,$db_pass);
        DB::connection('temp');
        $admin_model = \config('installer.admin_model',App\Admin::class);
        $admin_table = \config('installer.admin_table','admins');
        try {
            $admin_data = [
                'name' => $admin_name,
                'email' => $admin_email,
                'username' => $admin_username,
                'password' => Hash::make($admin_password),
            ];
            if (!\config('installer.model_has_roles')){
                $admin_data['role'] = \config('installer.super_admin_role_id',3);
            }
            $admin_id =  DB::connection('temp')->table($admin_table)->insertGetId($admin_data);

            if(\config('installer.model_has_roles')){
                DB::connection('temp')->table('model_has_roles')->insert([
                    'role_id' => \config('installer.super_admin_role_id',3),
                    'model_type' => self::sanitize_class_string($admin_model),
                    'model_id' => $admin_id,
                ]);
            }

            return [
                'type' => 'success',
                'msg' => 'Admin create successfully'
            ];

        }catch(\Exception $exception){
            return [
                'type' => 'danger',
                'msg' => 'Admin create failed'
            ];
        }

    }

    private static function sanitize_class_string($classString)
    {
        // Remove the leading backslash if it exists
        if (substr($classString, 0, 1) === '\\') {
            $classString = substr($classString, 1);
        }

        // Remove the trailing ::class if it exists
        if (substr($classString, -7) === '::class') {
            $classString = substr($classString, 0, -7);
        }

        return $classString;
    }

    public static function reverse_to_default_env()
    {
        $envPath = base_path('.env');

        // The key-value pairs to add/update
        $keyValuePairs = [
            'APP_NAME' => config('installer.app_name', 'Laravel'),
            'APP_ENV' => 'production',
            'APP_KEY' => (new self())->generate_app_key(),
            'APP_DEBUG' => 'true',
            'APP_URL' => url('/')
        ];

        // Rebuild the .env content
        $newEnvContent = '';
        foreach ($keyValuePairs as $key => $value) {
            $newEnvContent .= "{$key}={$value}\n";
        }

        // Write the updated content directly to the .env file
        File::put($envPath, rtrim($newEnvContent, "\n"));

        return [
            'type' => 'success',
            'msg' => 'Environment file updated successfully.'
        ];
    }
    public static function remove_middleware($middleware)
    {
        \Illuminate\Support\Facades\Artisan::call('xgenious:remove-middleware', [
            'middleware' => $middleware
        ]);
    }

}
