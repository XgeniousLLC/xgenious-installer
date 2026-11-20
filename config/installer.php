<?php

return [
    'app_name' => 'Fundorex', // must use one word, like Fundorex or Nazmart
    'super_admin_role_id' => 3,
    'admin_model' => \App\Admin::class,
    'admin_table' => 'admins',
    'multi_tenant' => false,
    'author' => 'xgenious',
    'product_key' => 'use_your_own_product_key_from_xgenious',
    'php_version' => '8.2',
    'database_type' => 'mysql', // mysql or pgsql

    // PostgreSQL tenant layout choices shown when database_type is pgsql and
    // multi_tenant is true. Empty array hides the selector.
    // key => label
    'database_layouts' => [
        'per_tenant' => 'Multi-database — one database per tenant',
        'pooled' => 'Three shared databases — landlord + tenant (RLS) + horizon',
    ],
    'database_layout' => 'per_tenant', // default selection

    // Optional product callback that receives the selected layout and the
    // submitted database credentials. It must expose:
    //   public function apply(string $layout, array $db): array
    // returning ['ok' => bool, 'message' => string].
    // The installer package intentionally knows nothing about product-specific
    // env keys or migration commands.
    'install_callback' => null,

    'extensions' => ['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'Tokenizer', 'XML', 'cURL', 'fileinfo'],
    'website' => 'https://xgenious.com',
    'email' => 'support@xgenious.com',
    'env_example_path' => public_path('env-sample.txt'),
    'broadcast_driver' => 'log',
    'cache_driver' => 'file',
    'queue_connection' => 'sync',
    'mail_port' => '587',
    'mail_encryption' => 'tls',
    'model_has_roles' => true,
    'bundle_pack' => false,
    'bundle_pack_key' => 'use_your_own_product_key_from_xgenious',

    // Name of the sibling directory (relative to the document root) that holds
    // statically-served assets (js/css/uploads) outside the Laravel app root.
    // Only used if this directory actually exists — safe no-op otherwise.
    'assets_dir' => 'assets',

    // How long to wait (seconds) when the installer self-checks whether its
    // own .env file is publicly reachable over HTTP.
    'license_env_check_timeout' => 4,
];