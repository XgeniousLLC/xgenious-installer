<?php

return [
    'app_name' => 'Fundorex', // must use one word, like Fundorex or Nazmart
    'super_admin_role_id' => 3,
    'admin_model' => \App\Admin::class,
    'admin_table' => 'admins',
    'multi_tenant' => false,
    'author' => 'xgenious',
    'product_key' => 'f1ad0b55fb09a9fb4935d26a76ce5e4d1f388df2',
    'php_version' => '8.3',
    'database_type' => 'mysql', // mysql or pgsql
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
    'bundle_pack_key' => 'f1ad0b55fb09a9fb4935d26a76ce5e4d1f388df2',
];