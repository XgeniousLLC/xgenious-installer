<?php

namespace Xgenious\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Xgenious\Installer\Helpers\InstallationHelper;
use Xgenious\Installer\Helpers\CacheCleaner;

class InstallerController extends Controller
{
    public function index()
    {
        if (!InstallationHelper::isInstallerNeeded()) {
            return url("/");
        }
        return view("installer::installer.index");
    }

    public function verifyPurchase(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "source" => "nullable|in:envato,direct",
            "en_email" => "nullable|email",
            "en_username" => "required_if:source,envato",
            "en_purchase_code" => "required",
        ]);
        if ($validation->fails()) {
            return response()->json([
                "type" => "danger",
                "msg" => "username or purchase code missing",
            ]);
        }

        $source = $request->input('source', 'envato');
        $en_username = $request->en_username;
        $en_purchase_code = $request->en_purchase_code;
        if (empty($en_username)) {
            // license.xgenious.com's validator currently requires a username
            // unconditionally, even for XGENIOUS-prefixed direct-purchase codes
            // it never actually checks it against. Backfill so direct buyers
            // aren't asked for an "Envato username" they don't have.
            $en_username = $request->en_email ?: $en_purchase_code;
        }
        $domain = url("/");
        $url = InstallationHelper::$api_path;
        $puuid = config("installer.product_key");

        if (config("installer.bundle_pack", false)) {
            $puuid = config("installer.bundle_pack_key");
        }

        if (empty($puuid)) {
            return response()->json([
                "type" => "danger",
                "msg" => "product key is missing",
            ]);
        }

        // Clean up any previous SQL dumps before re-verifying
        try {
            Storage::disk("local")->delete("database.sql");
            Storage::disk("local")->delete("database_pgsql.sql");
        } catch (\Exception $e) {
            // Ignore deletion errors
        }

        try {
            $response = Http::get($url, [
                "puid" => $puuid,
                "source" => $source,
                "en_username" => $en_username,
                "en_purchase_code" => $en_purchase_code,
                "ip" => $request->ip(),
                "user_agent" => $request->header("User-Agent"),
                "domain" => $domain,
                "email" => $request->en_email,
            ]);

            $headers = $response->headers();
            $body = $response->body();

            // Check if the response is a SQL file attachment
            $databaseType = $headers["Database-Type"][0] ?? 'mysql';
            $contentType = $headers["Content-Type"][0] ?? '';
            $contentDisposition = $headers["Content-Disposition"][0] ?? '';

            if (
                str_contains($contentType, 'application/sql') ||
                str_contains($contentDisposition, 'attachment')
            ) {
                if ($databaseType === 'postgresql') {
                    Storage::disk("local")->put("database_pgsql.sql", $body);
                } else {
                    Storage::disk("local")->put("database.sql", $body);
                }

                // This is the real success path — verified purchases stream the
                // SQL file directly rather than returning a JSON body. Tier
                // info (if any) comes back as the X-Variant-Name header
                // InstallationVerifyController already sets — purely
                // informational for display, nothing here is persisted.
                return response()->json([
                    "type" => "success",
                    "msg" => "Verification Success",
                    "license_tier" => $headers["X-Variant-Name"][0] ?? null,
                ]);
            }

            // Otherwise, expect a JSON response.
            $result = $response->json();
            $verified = !empty($result["verify"]);

            return response()->json([
                "type" => $verified ? "success" : "danger",
                "msg" => $result["msg"]
                    ?? "Could not connect to the server to verify your purchase. If you continue to get this message, contact our support.",
                "license_tier" => $result['license_tier'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "type" => "danger",
                "msg" => $e->getMessage(),
            ]);
        }
    }

    /**
     * System-readiness checks for the wizard's "System readiness" step.
     * Read-only — nothing here mutates the filesystem. See autoFix() for that.
     */
    public function checkSystem()
    {
        $extensions = [];
        foreach (InstallationHelper::extensions() as $ext) {
            $extensions[$ext] = InstallationHelper::extension_check($ext);
        }

        $folders = [];
        foreach (InstallationHelper::folders() as $folder) {
            $folders[$folder] = InstallationHelper::folder_permission($folder);
        }

        return response()->json([
            'php_version' => InstallationHelper::php_version(),
            'extensions' => $extensions,
            'folders' => $folders,
            'htaccess' => InstallationHelper::htaccess_status(),
            'assets' => InstallationHelper::assets_permission_status(),
            'uploads' => InstallationHelper::uploads_permission_status(),
            'env_exposure' => InstallationHelper::env_exposure_status(),
            'database_file' => InstallationHelper::has_database_file(),
            'is_local' => InstallationHelper::is_local_request(request()),
            'is_secure' => request()->secure(),
        ]);
    }

    /**
     * Applies every auto-fixable remediation from the readiness step, then
     * returns the same shape as checkSystem() so the frontend can re-render
     * from one response. Whatever can't be fixed here (AllowOverride/mod_rewrite)
     * simply comes back unchanged — that's the "needs your host" case.
     */
    public function autoFix()
    {
        InstallationHelper::fix_htaccess();
        InstallationHelper::fix_assets_permissions();
        InstallationHelper::fix_uploads_permission();

        return $this->checkSystem();
    }

    public function checkDatabase(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "db_driver" => "required|string|in:mysql,pgsql",
            "db_name" => "required",
            "db_username" => "required",
            "db_host" => "required",
            "db_password" => "nullable",
        ]);
        if ($validation->fails()) {
            return response()->json([
                "type" => "danger",
                "msg" => "make sure you have enter all the database details",
            ]);
        }
        $db_connection = InstallationHelper::check_database_connection(
            $request->db_driver,
            $request->db_host,
            $request->db_name,
            $request->db_username,
            $request->db_password
        );
        if ($db_connection["status"] === false) {
            return response()->json([
                "type" => "danger",
                "msg" => $db_connection["msg"],
            ]);
        }
        // Implement database connection check
        return response()->json([
            "type" => "success",
            "msg" => "Database connection successful",
        ]);
    }

    public function install(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "db_driver" => "required|string|in:mysql,pgsql",
            "db_name" => "required",
            "db_username" => "required",
            "db_host" => "required",
            "db_password" => "nullable",
            "admin_email" => "required|email",
            "admin_password" => "required",
            "admin_username" => "required",
            "admin_name" => "required",
        ]);
        if ($validation->fails()) {
            return response()->json([
                "type" => "danger",
                "msg" =>
                    "make sure you have enter all the database and admin informtaion",
            ]);
        }

        $keyValuePairs = [
            "APP_DEBUG" => "true",
            "APP_URL" => trim(url("/"), "/"),
            "DB_CONNECTION" => $request->db_driver,
            "DB_HOST" => $request->db_host,
            "DB_PORT" => $request->db_driver === 'pgsql' ? 5432 : 3306,
            "DB_DATABASE" => $request->db_name,
            "DB_USERNAME" => $request->db_username,
            "DB_PASSWORD" => is_null($request->db_password)
                ? ""
                // Escape internal double-quotes so a password containing one
                // can't close the quoted value early and let whatever follows
                // be interpreted as additional .env directives.
                : '"'.str_replace('"', '\\"', $request->db_password).'"',
            "BROADCAST_DRIVER" => config("installer.broadcast_driver", "log"),
            "CACHE_DRIVER" => config("installer.cache_driver", "file"),
            "QUEUE_CONNECTION" => config("installer.queue_connection", "sync"),
            "MAIL_PORT" => config("installer.mail_port", "587"),
            "MAIL_ENCRYPTION" => config("installer.mail_encryption", "tls"),
        ];
        $tenant_msg = "";
        if (config("installer.multi_tenant", false)) {
            $keyValuePairs["CENTRAL_DOMAIN"] = $request->getHost();
            $keyValuePairs["TENANT_DATABASE_PREFIX"] =
                \Str::kebab(config("installer.app_name", "multitenant")) . "_tenant_db_";
            $tenant_msg =
                'do not forget to setup wildcard subdomain in order to create subdomain by the system automatically <a target="_blank" href="https://docs.xgenious.com/docs/nazmart-multi-tenancy-ecommerce-platform-saas/wildcard-subdomain-configuration/"><i class="las la-external-link-alt"></i></a>';
        }

        // generate htaccess file
        InstallationHelper::generate_htaccess_file();

        //generate env file based on user and config file data
        InstallationHelper::generate_env_file($keyValuePairs);
        $db_host = $request->db_host;
        $db_name = $request->db_name;
        $db_user = $request->db_username;
        $db_pass = $request->db_password;
        // write helper for insert sql file
        $db_import = InstallationHelper::insert_database_sql_file(
            $request->db_driver,
            $db_host,
            $db_name,
            $db_user,
            $db_pass
        );
        if ($db_import["type"] === "danger") {
            InstallationHelper::reverse_to_default_env();
            return response()->json([
                "type" => "danger",
                "msg" => 'failed to update env',
            ]);
        }
        $admin_email = $request->admin_email;
        $admin_password = $request->admin_password;
        $admin_username = $request->admin_username;
        $admin_name = $request->admin_name;

        //write helper for create admin using the admin info
        InstallationHelper::create_admin(
            $request->db_driver,
            $admin_email,
            $admin_password,
            $admin_username,
            $admin_name,
            $db_host,
            $db_name,
            $db_user,
            $db_pass
        );

        // Licensing and version tracking (site_license_key, site_script_version)
        // are already owned by the product's own "General Settings > Check
        // Update" feature (xgenious/xgapiclient) once the admin activates —
        // the installer doesn't duplicate that here.

        // remove cache file
        CacheCleaner::clearAllCaches();

        //remove demo middleware
        InstallationHelper::remove_middleware('\App\Http\Middleware\Demo::class');

        $msg =
            "Installation Successful, if you still see install notice in your website, clear your browser cache ";
        $msg .=
            '<a href="' .
            url("/") .
            '">visit website</a> <p>' .
            $tenant_msg .
            '. setup cron job for subscription system work properly here is article for it <a target="_blank" href="https://docs.xgenious.com/docs/nazmart-multi-tenancy-ecommerce-platform-saas/cron-job/"><i class="las la-external-link-alt"></i></a></p>'; //write instruction message for multi tenant or normal script
        return response()->json([
            "type" => "success",
            "msg" => $msg,
            "tenant_note" => $tenant_msg,
        ]);
    }

    public function checkDatabaseExists()
    {
        // check database SQL file exists or not (MySQL or PostgreSQL dump)
        if (InstallationHelper::has_database_file()) {
            return response()->json([
                "type" => "success",
                "msg" => "Installation database SQL file found",
            ]);
        }

        return response()->json([
            "type" => "danger",
            "msg" =>
                "Your installation SQL file (database.sql or database_pgsql.sql) is missing, redownload files from codecanyon, or contact support",
        ]);
    }
}
