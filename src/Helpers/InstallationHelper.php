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
        // check storage has the database file (MySQL or PostgreSQL) or not
        return Storage::disk('local')->exists('database.sql')
            || Storage::disk('local')->exists('database_pgsql.sql');
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


    public static function check_database_connection($db_driver, $db_host, $db_name, $db_user, $db_pass)
    {
        self::set_temp_db_connection($db_driver, $db_host, $db_name, $db_user, $db_pass);
        try {
            DB::connection('temp')->getPdo();
            return ['status' => true, 'msg' => 'connection successful'];
        } catch (\Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    private static function set_temp_db_connection($db_driver, $db_host, $db_name, $db_user, $db_pass)
    {
        $defaultPort = Config::get("database.connections.$db_driver.port");
        if (empty($defaultPort)) {
            $defaultPort = $db_driver === 'pgsql' ? 5432 : 3306;
        }

        $connection = [
            'driver'   => $db_driver,
            'host'     => $db_host,
            'port'     => $defaultPort,
            'database' => $db_name,
            'username' => $db_user,
            'password' => is_null($db_pass) ? "" : $db_pass,
            'prefix'   => '',
            'prefix_indexes' => true,
        ];

        if ($db_driver === 'mysql') {
            // MySQL config
            $connection['charset']   = 'utf8mb4';
            $connection['collation'] = 'utf8mb4_unicode_ci';
            $connection['strict']    = true;
            $connection['engine']    = null;
            if (extension_loaded('pdo_mysql')) {
                $connection['options'] = array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]);
            }
        } else if ($db_driver === 'pgsql') {
            // PostgreSQL config
            $connection['charset'] = 'utf8';
            $connection['search_path'] = 'public';
            $connection['sslmode']     = 'prefer';
        }

        Config::set('database.connections.temp', $connection);

        DB::purge('temp');
        DB::reconnect('temp');
    }

    public static function generate_htaccess_file()
    {
        // Path where .htaccess should be generated
        $htaccessPath = base_path('../.htaccess');

        // If it already exists, do nothing
        if (File::exists($htaccessPath)) {
            return;
        }

        $htaccessSamplePath = __DIR__ . '/../../htaccess-sample.txt';

        if (File::exists($htaccessSamplePath)) {
            $content = File::get($htaccessSamplePath);
        } else {
            // Fallback default .htaccess (Laravel standard)
            $content = <<<HTACCESS
    <IfModule mod_rewrite.c>
        <IfModule mod_negotiation.c>
            Options -MultiViews -Indexes
        </IfModule>

        RewriteEngine On

        # Handle Authorization Header
        RewriteCond %{HTTP:Authorization} .
        RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

        # Redirect Trailing Slashes If Not A Folder...
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_URI} (.+)/$
        RewriteRule ^ %1 [L,R=301]

        # Send Requests To Front Controller...
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </IfModule>
    HTACCESS;
        }

        // Write the file
        File::put($htaccessPath, $content);
    }

    public static function generate_env_file(array $keyValuePairs)
    {
        $envSamplePath = self::has_env_sample_file() ? config('installer.env_example_path') : __DIR__ . '/../../env-sample.txt';

        $envPath = base_path('.env');

        $envContent = File::get($envSamplePath);

        // Generate APP_KEY if not provided
        if (!isset($keyValuePairs['APP_KEY'])) {
            $keyValuePairs['APP_KEY'] = (new self())->generate_app_key();
        }

        // Dynamically detect APP_ENV
        if (!isset($keyValuePairs['APP_ENV'])) {
            $appUrl = $keyValuePairs['APP_URL'] ?? url('/');
            $host = parse_url($appUrl, PHP_URL_HOST);
            $port = parse_url($appUrl, PHP_URL_PORT);

            $localPatterns = ['localhost', '127.0.0.1', '.test', '.local'];
            $isLocal = false;

            foreach ($localPatterns as $pattern) {
                if (strpos($host, $pattern) !== false) {
                    $isLocal = true;
                    break;
                }
            }

            if (!$isLocal && in_array($port, [8000, 8001, 8002])) {
                $isLocal = true;
            }

            $keyValuePairs['APP_ENV'] = $isLocal ? 'local' : 'production';
        }

        // Replace or add keys dynamically
        foreach ($keyValuePairs as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                // Add new key at the end
                $envContent .= PHP_EOL . "{$key}={$value}";
            }
        }

        $placeholders = [
            'YOUR_APP_URL'      => $keyValuePairs['APP_URL'] ?? 'http://localhost',
            'YOUR_APP_ENV'      => $keyValuePairs['APP_ENV'] ?? 'production',
            'YOUR_DATABASE_HOST'=> $keyValuePairs['DB_HOST'] ?? '127.0.0.1',
            'YOUR_DATABASE_NAME'=> $keyValuePairs['DB_DATABASE'] ?? 'laravel',
            'YOUR_DATABASE_USERNAME'=> $keyValuePairs['DB_USERNAME'] ?? 'laravel',
            'YOUR_DATABASE_PASSWORD'=> $keyValuePairs['DB_PASSWORD'] ?? '',
        ];

        $envContent = str_replace(array_keys($placeholders), array_values($placeholders), $envContent);

        // Write the final .env
        File::put($envPath, $envContent);
    }

    private function generate_app_key()
    {
        return 'base64:' . base64_encode(Str::random(32));
    }

    public  static  function insert_database_sql_file($db_driver, $db_host, $db_name, $db_user, $db_pass)
    {
        if ($db_driver === 'pgsql') {
            return self::insert_pgsql_database($db_host, $db_name, $db_user, $db_pass);
        }

        try {

            $dsn = "{$db_driver}:host={$db_host};dbname={$db_name}";
            $db = new \PDO($dsn, $db_user, $db_pass);
            // set the PDO error mode to exception
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $sql_file = $db_driver === 'pgsql' ? 'database_pgsql.sql' : 'database.sql';

            if (!Storage::disk('local')->exists($sql_file)) {
                return [
                    'type' => 'danger',
                    'msg' => sprintf('SQL file "%s" not found.', $sql_file),
                ];
            }

            $query = Storage::drive('local')->get($sql_file);
            $stmt = $db->prepare($query);
            $result = (bool)$stmt->execute();

            return [
                'type' => $result ? 'success' : 'danger',
                'msg' => $result ? 'Database insert success' : 'Database insert failed.',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'danger',
                'msg' => 'Database insert failed: ' . $e->getMessage(),
            ];
        }
    }

    public static  function create_admin($db_driver, $admin_email, $admin_password, $admin_username, $admin_name, $db_host, $db_name, $db_user, $db_pass)
    {
        self::set_temp_db_connection($db_driver, $db_host, $db_name, $db_user, $db_pass);
        DB::connection('temp');
        $admin_model = \config('installer.admin_model', App\Admin::class);
        $admin_table = \config('installer.admin_table', 'admins');
        try {
            $admin_data = [
                'name' => $admin_name,
                'email' => $admin_email,
                'username' => $admin_username,
                'password' => Hash::make($admin_password),
            ];
            if (!\config('installer.model_has_roles')) {
                $admin_data['role'] = \config('installer.super_admin_role_id', 3);
            }
            $admin_id =  DB::connection('temp')->table($admin_table)->insertGetId($admin_data);

            if (\config('installer.model_has_roles')) {
                DB::connection('temp')->table('model_has_roles')->insert([
                    'role_id' => \config('installer.super_admin_role_id', 3),
                    'model_type' => self::sanitize_class_string($admin_model),
                    'model_id' => $admin_id,
                ]);
            }

            return [
                'type' => 'success',
                'msg' => 'Admin create successfully'
            ];
        } catch (\Exception $exception) {
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

    public static function insert_pgsql_database($host, $db, $user, $pass)
    {
        $sqlFile = storage_path('app/database_pgsql.sql');
        $errorLog = storage_path('app/import_error.txt');

        file_put_contents($errorLog, ""); // reset

        if (!file_exists($sqlFile)) {
            return ["type" => "danger", "msg" => "PostgreSQL SQL file not found"];
        }

        $connStr = sprintf(
            "host=%s port=5432 dbname=%s user=%s password=%s options='--client_encoding=UTF8'",
            $host,
            $db,
            $user,
            $pass
        );

        $conn = @pg_connect($connStr);

        if (!$conn) {
            file_put_contents($errorLog, "Connection failed: " . pg_last_error() . "\n", FILE_APPEND);
            return ["type" => "danger", "msg" => "PostgreSQL connection failed"];
        }

        $content = file_get_contents($sqlFile);
        if ($content === false) {
            return ["type" => "danger", "msg" => "Unable to read SQL file"];
        }

        // Normalize line breaks
        $content = str_replace("\r", "\n", $content);

        $lines = explode("\n", $content);
        $buffer = '';
        $copyMode = false;
        $copyBuffer = '';
        $lineNum = 0;

        pg_query($conn, "BEGIN");

        foreach ($lines as $line) {
            $lineNum++;
            $trim = trim($line);

            // Skip comments
            if (!$copyMode && (str_starts_with($trim, '--') || $trim === '')) {
                continue;
            }

            // COPY blocks
            if (preg_match('/^COPY\s+.*FROM\s+stdin/i', $line)) {
                $copyMode = true;
                $copyBuffer = $line . "\n";
                continue;
            }

            if ($copyMode) {
                $copyBuffer .= $line . "\n";
                if ($trim === '\.') {
                    // End of COPY block
                    if (!pg_query($conn, $copyBuffer)) {
                        file_put_contents($errorLog, "COPY error at line $lineNum: " . pg_last_error($conn) . "\n", FILE_APPEND);
                        pg_query($conn, "ROLLBACK");
                        return ["type" => "danger", "msg" => "COPY block failed"];
                    }
                    $copyMode = false;
                    $copyBuffer = '';
                }
                continue;
            }

            // Compose regular SQL
            $buffer .= $line . "\n";

            if (str_ends_with($trim, ';')) {
                $query = trim($buffer);
                if ($query !== '') {
                    if (!pg_query($conn, $query)) {
                        file_put_contents(
                            $errorLog,
                            "SQL error at line $lineNum\n" .
                                pg_last_error($conn) . "\n" .
                                "Query: " . substr($query, 0, 500) . "\n\n",
                            FILE_APPEND
                        );
                        pg_query($conn, "ROLLBACK");
                        return ["type" => "danger", "msg" => "SQL execution failed"];
                    }
                }
                $buffer = "";
            }
        }

        // Last buffer
        if (trim($buffer) !== "") {
            if (!pg_query($conn, $buffer)) {
                file_put_contents($errorLog, "SQL error (end): " . pg_last_error($conn) . "\n", FILE_APPEND);
                pg_query($conn, "ROLLBACK");
                return ["type" => "danger", "msg" => "Final SQL statement failed"];
            }
        }

        pg_query($conn, "COMMIT");

        return ["type" => "success", "msg" => "PostgreSQL DB imported successfully"];
    }
}
