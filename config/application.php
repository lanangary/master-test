<?php
#------------------------------------------------------
# DO NOT EDIT
#------------------------------------------------------

if (PHP_SAPI != 'cli') {
    $scheme = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '');
    $app['host'] = $scheme.'://'.$_SERVER['HTTP_HOST'];
}

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$webroot_dir = $root_dir . '/www';

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = Dotenv\Dotenv::create($root_dir);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
    $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
}

/**
 * Set up our global environment constant and load its config first
 * Default: development
 */
define('WP_ENV', env('WP_ENV') ?? 'development');

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * Set configuration based on
 */
if (WP_ENV === 'production') {
    define('SAVEQUERIES', false);
    define('SCRIPT_DEBUG', false);
    define('WP_DEBUG', false);
    define('WP_DEBUG_LOG', false);
    define('WP_DEBUG_DISPLAY', false);
    if (!defined('FORCE_SSL_ADMIN')) {
        define('FORCE_SSL_ADMIN', true);
    }
    @ini_set('display_errors', 0);
} else {
    define('DONOTCACHEPAGE', true); //Page caching off in development
    define('SAVEQUERIES', true);
    define('SCRIPT_DEBUG', true);
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', false);
    @ini_set('display_errors', 1);
}

/**
 * URLs
 */
if(!empty(env('WP_HOME') ?? '')){
    define('WP_HOME', env('WP_HOME'));
    define('WP_SITEURL', env('WP_SITEURL') ?? 'https://localhost/wp');
}elseif(isset($app['host'])){
    define('WP_HOME', $app['host']);
    define('WP_SITEURL', WP_HOME . '/wp');
}else{
    define('WP_HOME', 'https://localhost');
    define('WP_SITEURL', 'https://localhost/wp');
}

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/app');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);

/**
 * DB settings
 */
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASSWORD', env('DB_PASSWORD'));
define('DB_HOST', env('DB_HOST') ?? 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?? 'wp_';

/**
 * Authentication Unique Keys and Salts
 */
define('AUTH_KEY', env('AUTH_KEY'));
define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
define('NONCE_KEY', env('NONCE_KEY'));
define('AUTH_SALT', env('AUTH_SALT'));
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', env('AUTOMATIC_UPDATER_DISABLED') ?? true);
define('AUTOSAVE_INTERVAL', env('AUTOSAVE_INTERVAL') ?? 300);
define('CONCATENATE_SCRIPTS', env('CONCATENATE_SCRIPTS') ?? true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?? true);
define('EMPTY_TRASH_DAYS', env('EMPTY_TRASH_DAYS') ?? 10);
define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?? 10);
define('WP_MEMORY_LIMIT', env('WP_MEMORY_LIMIT') ?? '512M');
define('WP_DEFAULT_THEME', env('WP_THEME') ?? 'wp');

if (!defined('DISALLOW_FILE_MODS')) {
    define('DISALLOW_FILE_MODS', env('DISALLOW_FILE_MODS') ?? true);
}

if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', env('DISALLOW_FILE_EDIT') ?? true);
}

/**
 * AWS access keys
 */
if (!empty(env('AWS_ACCESS_KEY_ID')) && !empty(env('AWS_SECRET_ACCESS_KEY'))) {
    define('AS3CF_SETTINGS', serialize(array(
        'provider' => 'aws',
        'access-key-id' => env('AWS_ACCESS_KEY_ID'),
        'secret-access-key' => env('AWS_SECRET_ACCESS_KEY'),
    )));
}

/**
 * Gravity form keys
 */

if (!empty(env('GF_LICENSE_KEY'))) {
    define('GF_LICENSE_KEY', env('GF_LICENSE_KEY'));
}
if (!empty(env('GF_RECAPTCHA_PUBLIC_KEY'))) {
    define('GF_RECAPTCHA_PUBLIC_KEY', env('GF_RECAPTCHA_PUBLIC_KEY'));
}
if (!empty(env('GF_RECAPTCHA_PRIVATE_KEY'))) {
    define('GF_RECAPTCHA_PRIVATE_KEY', env('GF_RECAPTCHA_PRIVATE_KEY'));
}

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
