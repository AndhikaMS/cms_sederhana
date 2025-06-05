<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'name' => 'CMS Sederhana',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes.
    |
    */
    'env' => 'development',

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */
    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */
    'url' => 'http://localhost/cms_sederhana',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions.
    |
    */
    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */
    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */
    'key' => 'base64:'.base64_encode(random_bytes(32)),

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the session configuration for your application.
    |
    */
    'session' => [
        'driver' => 'file',
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => dirname(__DIR__) . '/storage/sessions',
        'connection' => null,
        'table' => 'sessions',
        'lottery' => [2, 100],
        'cookie' => 'cms_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the cookie configuration for your application.
    |
    */
    'cookie' => [
        'lifetime' => 43200,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the database configuration for your application.
    |
    */
    'database' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'cms_sederhana',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the mail configuration for your application.
    |
    */
    'mail' => [
        'driver' => 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.mailtrap.io',
        'port' => getenv('MAIL_PORT') ?: '2525',
        'username' => getenv('MAIL_USERNAME') ?: null,
        'password' => getenv('MAIL_PASSWORD') ?: null,
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from' => [
            'address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com',
            'name' => getenv('MAIL_FROM_NAME') ?: 'CMS Sederhana',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the upload configuration for your application.
    |
    */
    'upload' => [
        'max_size' => 5242880, // 5MB
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'path' => dirname(__DIR__) . '/public/uploads',
        'url' => '/uploads',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the cache configuration for your application.
    |
    */
    'cache' => [
        'driver' => 'file',
        'path' => dirname(__DIR__) . '/storage/cache',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the log configuration for your application.
    |
    */
    'log' => [
        'driver' => 'file',
        'path' => dirname(__DIR__) . '/storage/logs',
        'level' => 'debug',
        'days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the security configuration for your application.
    |
    */
    'security' => [
        'password_min_length' => 8,
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_hash_options' => [
            'cost' => 12,
        ],
        'remember_token_lifetime' => 43200, // 30 days
        'reset_token_lifetime' => 3600, // 1 hour
        'invite_token_lifetime' => 604800, // 7 days
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the pagination configuration for your application.
    |
    */
    'pagination' => [
        'per_page' => 10,
        'page_range' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the theme configuration for your application.
    |
    */
    'theme' => [
        'name' => 'AdminLTE',
        'version' => '3.2.0',
        'assets' => [
            'css' => [
                '/assets/css/adminlte.min.css',
                '/assets/plugins/fontawesome-free/css/all.min.css',
                '/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css',
                '/assets/plugins/daterangepicker/daterangepicker.css',
                '/assets/plugins/summernote/summernote-bs4.min.css',
                '/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css',
                '/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css',
                '/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css',
                '/assets/plugins/select2/css/select2.min.css',
                '/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',
                '/assets/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css',
                '/assets/plugins/bs-stepper/css/bs-stepper.min.css',
                '/assets/plugins/dropzone/min/dropzone.min.css',
                '/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css',
                '/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css',
                '/assets/plugins/jqvmap/jqvmap.min.css',
                '/assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
                '/assets/plugins/toastr/toastr.min.css',
            ],
            'js' => [
                '/assets/plugins/jquery/jquery.min.js',
                '/assets/plugins/bootstrap/js/bootstrap.bundle.min.js',
                '/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js',
                '/assets/plugins/daterangepicker/daterangepicker.js',
                '/assets/plugins/summernote/summernote-bs4.min.js',
                '/assets/plugins/datatables/jquery.dataTables.min.js',
                '/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
                '/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js',
                '/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js',
                '/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js',
                '/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js',
                '/assets/plugins/jszip/jszip.min.js',
                '/assets/plugins/pdfmake/pdfmake.min.js',
                '/assets/plugins/pdfmake/vfs_fonts.js',
                '/assets/plugins/datatables-buttons/js/buttons.html5.min.js',
                '/assets/plugins/datatables-buttons/js/buttons.print.min.js',
                '/assets/plugins/datatables-buttons/js/buttons.colVis.min.js',
                '/assets/plugins/select2/js/select2.full.min.js',
                '/assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js',
                '/assets/plugins/moment/moment.min.js',
                '/assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js',
                '/assets/plugins/bs-stepper/js/bs-stepper.min.js',
                '/assets/plugins/dropzone/min/dropzone.min.js',
                '/assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js',
                '/assets/plugins/jquery-validation/jquery.validate.min.js',
                '/assets/plugins/jquery-validation/additional-methods.min.js',
                '/assets/plugins/sweetalert2/sweetalert2.min.js',
                '/assets/plugins/toastr/toastr.min.js',
                '/assets/js/adminlte.min.js',
            ],
        ],
    ],
]; 