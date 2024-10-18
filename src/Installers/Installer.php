<?php

namespace WordpressBase\Installers;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installer
{
    private static $removeDirs = [
        'www/wp/wp-content/themes/twentyten',
        'www/wp/wp-content/themes/twentyeleven',
        'www/wp/wp-content/themes/twentytwelve',
        'www/wp/wp-content/themes/twentythirteen',
        'www/wp/wp-content/themes/twentyfourteen',
        'www/wp/wp-content/themes/twentyfifteen',
        'www/wp/wp-content/themes/twentysixteen',
        'www/wp/wp-content/themes/twentyseventeen',
        'www/wp/wp-content/themes/twentynineteen',
        'www/wp/wp-content/themes/twentytwenty',
    ];

    private static $removeFiles = [
        'www/wp/license.txt',
        'www/wp/robots.txt',
        'www/wp/readme.html',
        'www/wp/wp-content/plugins/hello.php',
    ];

    private static $configFiles = [
        '.env.template',
        '.env.staging',
        '.env.live'
    ];

    public static function preInstall(Event $event)
    {
    }

    public static function checkHtaccess(Event $event){
        // Provides access to the current Composer instance.
        $composer = $event->getComposer();
        $io = $event->getIO();

        // Check if site has been setup previously.
        if (file_exists('www/.htaccess')) {
            return;
        }

        $io->write('<warning>[WARNING]</warning> .htaccess not found');
        $content = "# BEGIN WordPress

RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]

# END WordPress";

        $htaccess = fopen('www/.htaccess', 'w');
        fwrite($htaccess, $content);
        fclose($htaccess);

        if (file_exists('www/.htaccess')) {
            $io->write('<info>[INFO]</info> .htaccess created!');
            return;
        }

        $io->write('<warning>[WARNING]</warning> .htaccess failed!');
    }

    public static function postInstall(Event $event)
    {
        // Provides access to the current Composer instance.
        $composer = $event->getComposer();
        $io = $event->getIO();

        // Check to see we have an ACF auth file
        self::checkACFAuth($event);

        // Check if site has been setup previously.
        if (file_exists('.env') || !file_exists('www/app/themes/wp/style.css')) {
            return;
        }

        self::juiceboxAscii($io);
        self::writeSection($io, "WordPress Install");

        $dbPrefix = strtolower(self::randomString()) . '_';
        $wordPressSecrets = self::generateWordPressSecrets();
        $adminPassword = '';
        $boxDomain = '';

        // User prompts
        $humanThemeName = ucwords(self::askQuestion($io, 'Enter desired human-readable theme name [<options=bold>Juicebox Base Theme</>]: ', 'Juicebox Base Theme'));
        $themeName = strtolower(self::askQuestion($io, 'Enter desired theme folder name [<options=bold>juicebox-base</>]: ', 'juicebox-base'));
        $dbName = strtolower(self::askQuestion($io, 'Enter desired database name [<options=bold>' . $themeName . '</>]: ', $themeName));
        $dbHost = strtolower(self::askQuestion($io, 'Enter database host address for local dev [<options=bold>dev.box</>]: ', 'dev.box'));
        $dbUser = strtolower(self::askQuestion($io, 'Enter database username for local dev [<options=bold>root</>]: ', 'root'));
        $dbPass = strtolower(self::askQuestion($io, 'Enter database password for local dev [<options=bold>charlie14</>]: ', 'charlie14'));

        $enableSourceMaps = strtolower(self::askQuestion($io, 'Do you want to enable source maps? [Yes/<options=bold>No</>]: ', 'no'));
        $enableBuildNotifications = strtolower(self::askQuestion($io, 'Do you want to enable build notifications (MacOS only)? [<options=bold>Yes</>/No]: ', 'yes'));
        $addDefaultContent = strtolower(self::askConfirmation($io, 'Do you want default content installed? [<options=bold>Yes</>/No]: ', 'yes'));
        $addGravityForms = strtolower(self::askConfirmation($io, 'Do you want gravityform setings and forms installed? [<options=bold>Yes</>/No]: ', 'yes'));
        $runYarn = strtolower(self::askConfirmation($io, 'Do you want to this to run yarn setup too? [<options=bold>Yes</>/No]: ', 'yes'));

        self::writeSection($io, "Configuring Environment");

        // Update .env template file contents
        if (!empty(self::$configFiles)) {
            $io->write('<info>[INFO]</info> Configure .env template');

            foreach (self::$configFiles as $fileName) {
                if (!is_file($fileName)) {
                    continue;
                }

                self::updateFileContent($fileName, '__THEMENAME__', $themeName);
                self::updateFileContent($fileName, '__DBNAME__', $dbName);
                self::updateFileContent($fileName, '__DBPREFIX__', $dbPrefix);
                self::updateFileContent($fileName, '__UNIQUEKEYS__', $wordPressSecrets);
                self::updateFileContent($fileName, '__GFLICENSEKEY__', '7bed219676e4dcda4001a02472d17797');
                self::updateFileContent($fileName, '__GFRECAPTCHASITE__', '6LdloQEVAAAAACT8PYtr9TZfsaqv14xmuncK4rV3');
                self::updateFileContent($fileName, '__RECAPTCHASECRET__', '6LdloQEVAAAAAEBDfe2KuVAv0yJLljUtBWmmv7Yw');
            }
        }

        // Change application name
        $io->write('<info>[INFO]</info> Update application/theme name');
        $io->write(self::updateFileContent('composer.json', 'juicebox/wordpress-base', 'juicebox/' . $themeName));
        $io->write(self::updateFileContent('composer.json', '__THEMENAME__', $themeName));
        $io->write(self::updateFileContent('package.json', '__THEMENAME__', $themeName));
        $io->write(self::updateFileContent('www/app/themes/wp/style.css', '__THEMENAME__', $humanThemeName));

        // Rename theme folder
        $io->write(self::updateThemeFolderName($themeName));

        // Create a local .env file.
        if (!file_exists('.env')) {
            if (copy('.env.template', '.env')) {
                $io->write('<info>[INFO]</info> Local .env file created');
                self::updateFileContent('.env', '__DBHOST__', $dbHost);
                self::updateFileContent('.env', '__DBUSER__', $dbUser);
                self::updateFileContent('.env', '__DBPASSWORD__', $dbPass);
                self::updateFileContent('.env', '__WPENV__', 'development');
                self::updateFileContent('.env', '__ENABLESOURCEMAPS__', $enableSourceMaps);
                self::updateFileContent('.env', '__ENABLEBUILDNOTIFICATIONS__', $enableBuildNotifications);
            } else {
                $io->write('<error>[ERROR]</error> Could not create local .env file');
            }
        } else {
            $io->write('<warning>[WARNING]</warning> Local .env file already exists. Skipping .env file creation');
        }

        self::writeSection($io, "Clean Up Installation");

        // Remove directories.
        if (!empty(self::$removeDirs)) {
            $io->write('<info>[INFO]</info> Removing ' . count(self::$removeDirs) . ' Directories');

            foreach (self::$removeDirs as $dir) {
                if (is_dir($dir)) {
                    chmod($dir, 0777);
                    self::deleteDir($dir);
                    $io->write('       - Removing <info>' . $dir . '</info>');
                }
            }
        }

        // Remove files.
        if (!empty(self::$removeFiles)) {
            $io->write('<info>[INFO]</info> Removing ' . count(self::$removeFiles) . ' Files');

            foreach (self::$removeFiles as $file) {
                if (is_file($file)) {
                    chmod($file, 0777);
                    unlink($file);
                    $io->write('       - Removing <info>' . $file . '</info>');
                }
            }
        }

        // Read in the env file.
        self::writeSection($io, "Setup WordPress");

        \Env::init();
        $dotenv = \Dotenv\Dotenv::create('.');
        $dotenv->load();

        if (env('WP_ENV') === 'development') {
            exec('wp --info', $output, $return);
            unset($output);

            if ($return !== 0) {
                $io->write('<error>[ERROR]</error> WP CLI does not appear to be installed. Skipping automatic install process');
            } else {
                // Check if the database exists
                exec('wp db check', $output, $return);
                unset($output);

                if ($return !== 0) { // if it does not, create it?
                    $io->write('<warning>[WARNING]</warning> database "<fg=yellow>' . $dbName . '</>" does not exist');
                    if (self::askConfirmation($io, 'Do you want to create a database named "<info>' . $dbName . '</info>" [<options=bold>Yes</>/no]: ', 'Yes')) {
                        exec('wp db create || true', $output, $return);
                        unset($output);

                        if ($return !== 0) {
                            $io->write('<info>[INFO]</info> Database created' . "\n");
                        }
                        $io->write("\n" . '<error>[ERROR]</error> Could not create the database. Create it manually before proceeding with the next step' . "\n");
                    }
                }

                // Check if WordPress is installed
                exec('wp core is-installed', $output, $return);
                unset($output);

                if ($return === 1) {
                    $io->write('<info>[INFO]</info> WordPress is not installed. Let\'s do that now' . "\n");

                    $boxDomain = strtolower(self::askQuestion($io, 'What is the local domain for this project? e.g site.name.box: ', ''));

                    $io->write("\n" . '<info>[INFO]</info> Installing WordPress...');
                    exec('wp core install --url="http://' . $boxDomain . '" --title="' . $humanThemeName . '" --admin_user=juicebox --admin_email=web@juicebox.com.au', $output, $return);

                    //Print out the password
                    $adminPassword = str_replace('Admin password:', '<info>[INFO]</info> <options=bold>Wordpress installed</>. WordPress Admin Password:', preg_grep('/Admin password:/m', $output));
                    unset($output);

                    //Activate theme
                    $io->write("\n" . '<info>[INFO]</info> Activating theme...');
                    exec('wp theme activate '.$themeName);

                    // Set permalink structure
                    exec('wp option set permalink_structure /%postname%/');
                    $io->write('<info>[INFO]</info> Permalink structure set to /%postname%/');

                    $io->write('<info>[INFO]</info> Flushing rewrite rules...');
                    exec('wp rewrite flush --hard');
                    $io->write('       - <options=bold>Success</>. Rewrite rules flushed.' . "\n");

                    // Activate common options
                    $io->write('<info>[INFO]</info> Updating Juicebox common website options');
                    exec('wp option update comments_notify 0');
                    exec('wp option update default_comment_status closed');
                    exec('wp option update default_ping_status closed');
                    exec('wp option update default_pingback_flag 0');
                    exec('wp option update comment_moderation 1');
                    exec('wp option update timezone_string "Australia/Perth"');

                    // Activate essential plugins
                    $io->write('<info>[INFO]</info> Activating plugins');
                    exec('wp plugin activate juicy');
                    exec('composer dump-autoload');
                    exec('wp plugin activate wp-h5bp-htaccess');
                    exec('wp plugin activate classic-editor');
                    exec('wp plugin activate gravityforms');
                    exec('wp plugin activate gravityformscli');
                    exec('wp plugin activate gravityformssendgrid');
                    exec('wp plugin activate gravity-forms-acf-field');
                    exec('wp option set juicy_settings \'{"enable_gravity_forms_hooks": "1"}\' --format=json');
                    exec('wp option set rg_gforms_captcha_type "invisible"');
                    exec('wp option set rg_gforms_currency "AUD"');

                    //Gravity form options
                    if($addGravityForms){
                        self::defaultForms($io, $themeName);
                    }

                    //Add default content
                    if($addDefaultContent){
                        self::defaultContent($io, $themeName);
                    }

                    // Show the admin password
                    $io->write($adminPassword);
                    $io->write('       - <options=underscore>Please save the admin password in 1password</>' . "\n");
                } else {
                    $io->write('Wordpress is already installed.');
                }
            }
        }

        self::writeSection($io, "Setup Complete! Next Steps...");

        //Add default content
        if($runYarn){
            $io->write('<info>[INFO]</info> Compiling frontend assets');
            exec('yarn && yarn run dev');
        }else{
            $io->write('Run <info>`yarn && yarn run dev`</info> to compile assets.');
            $io->write('Visit the plugins page to enable required plugins.' . "\n");
        }

        $log = [
            "Enter desired human-readable theme name: {$humanThemeName}",
            "Enter desired theme folder name: {$themeName}",
            "Enter desired database name: {$dbName}",
            "Enter database host address for local dev: {$dbHost}",
            "Enter database username for local dev: {$dbUser}",
            "Enter database password for local dev: {$dbPass}",
            "Do you want to enable source maps?: {$enableSourceMaps}",
            "Do you want to enable build notifications: {$enableBuildNotifications}",
            "Do you want to add default content? {$addDefaultContent}",
            "Do you want to this to run yarn setup too? {$runYarn}",
            "Do you want default content installed? {$addDefaultContent}",
            "Do you want gravityform setings and forms installed? {$addGravityForms}",
            "What is the local domain for this project? e.g site.name.box: {$boxDomain}",
            is_array($adminPassword) ? implode('', $adminPassword) : $adminPassword,
            "Completed: ".date('Y-m-d H:i:s')
        ];
        file_put_contents('installer.log', implode("\n", array_values($log)));

        self::writeSection($io, "All done! Happy building :)");
    }

    private static function copyDirContents($sourcePath, $destPath)
    {
        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                @mkdir($destPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $destPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    private static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $value) {
            if ($value->isFile()) {
                chmod($value, 0777);
                unlink($value);
            } else {
                rmdir($value);
            }
        }
        rmdir($dirPath);
    }

    private static function updateFileContent($fileName, $key, $value)
    {
        if (is_file($fileName)) {
            $configFile = file_get_contents($fileName);

            if (strpos($configFile, $key)) {
                $configFile = str_replace($key, $value, $configFile);
            }

            if (file_put_contents($fileName, $configFile)) {
                return '       - ' . $fileName;
            }
            return '<error>[ERROR]</error> There was a problem updating <fg=red>"' . $key . '"</> in <fg=red>"' . $fileName . '"</>';
        }
        return '<error>[ERROR]</error> Cannot update <fg=red>"' . $key . '"</> in <fg=red>"' . $fileName . '"</>. File does not exist';
    }

    /**
     * Generate unique authentication keys and salts.
     */
    private static function generateWordPressSecrets()
    {
        $uniqueKeys = '';
        $keys = [
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        ];

        foreach ($keys as $key) {
            $uniqueKeys .= $key . '=' . self::randomString(64, true) . "\n";
        }
        return $uniqueKeys;
    }

    private static function updateThemeFolderName($themeName)
    {
        if (is_dir('www/app/themes/wp')) {
            if (rename("www/app/themes/wp", "www/app/themes/{$themeName}")) {
                return '<info>[INFO]</info> Rename theme folder to "<info>' . $themeName . '</info>"';
            }
            return '<error>[ERROR]</error> Could not rename the theme folder.';
        }
        return '<error>[ERROR]</error> Could not rename the theme folder to <fg=red>"' . $themeName . '"</>. Default "wp" theme folder does not exist';
    }

    private static function randomString($length = 8, $specialChars = false)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($specialChars) {
            $characters .= "[{(!@#$%^/&*_+;?\:)}]";
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private static function writeSection($io, $message)
    {
        $io->write("\n" . '<bg=blue;fg=white> ' . strtoupper($message) . ' </>' . "\n");
    }

    private static function askQuestion($io, $question, $defaultValue)
    {
        return $io->ask('<fg=blue;options=bold>[QUESTION]</> ' . $question, $defaultValue);
    }

    private static function askConfirmation($io, $question, $defaultValue)
    {
        return $io->askConfirmation('<fg=blue;options=bold>[QUESTION]</> ' . $question, $defaultValue);
    }

    private static function juiceboxAscii($io)
    {
        $io->write("<fg=blue>       __   __    __   __    ______  _______ .______     ______   ___   ___ </>");
        $io->write("<fg=blue>      |  | |  |  |  | |  |  /      ||   ____||   _  \   /  __  \  \  \ /  / </>");
        $io->write("<fg=blue>      |  | |  |  |  | |  | |  ,----'|  |__   |  |_)  | |  |  |  |  \  V  /  </>");
        $io->write("<fg=blue>.--.  |  | |  |  |  | |  | |  |     |   __|  |   _  <  |  |  |  |   >   <   </>");
        $io->write("<fg=blue>|  `--'  | |  `--'  | |  | |  `----.|  |____ |  |_)  | |  `--'  |  /  .  \  </>");
        $io->write("<fg=blue> \______/   \______/  |__|  \______||_______||______/   \______/  /__/ \__\ </>");
        $io->write('');
    }

    private static function defaultContent($io, $themeName){
        $io->write('<info>[INFO]</info> Removing WordPress default pages, posts and comments');

        exec("wp post delete 1 --force");
        exec("wp post delete 2 --force");
        exec("wp post delete 3 --force");

        $io->write('<info>[INFO]</info> Adding default content');

        $menu_order = 1;
        $pages = [
            ['post_title' => 'Home Page', 'post_status' => 'publish', 'post_content' => '', 'page_template' => 'page--home-page.php'],
            ['post_title' => 'About', 'post_status' => 'publish', 'post_content' => '', 'page_template' => ''],
            ['post_title' => 'Blog', 'post_status' => 'publish', 'post_content' => '', 'page_template' => ''],
            ['post_title' => 'Contact', 'post_status' => 'publish', 'post_content' => '', 'page_template' => 'page--contact.php'],
            ['post_title' => 'Grid', 'post_status' => 'draft', 'post_content' => '', 'page_template' => '']
        ];
        foreach($pages as $key => $page){
            $pages[$key]['post_name'] = $page['post_name'] = strtolower(str_replace(' ', '-', $page['post_title']));
            $id = exec("wp post create --post_type=page --post_title='{$page['post_title']}' --post_status='{$page['post_status']}'  --post_content='{$page['post_content']}' --page_template='{$page['page_template']}' --post_name='{$page['post_name']}' --post_author=1 --menu_order='{$menu_order}' --porcelain");
            $pages[$key]['ID'] = $id;
            ++$menu_order;
        }

        $dir = new \DirectoryIterator("www/app/themes/{$themeName}/pages");

        $legal_pages = [];
        foreach ($dir as $dirinfo) {
            if (!$dirinfo->isDot()) {
                $post_title = $dirinfo->getBasename(".{$dirinfo->getExtension()}");
                $post_name = strtolower(str_replace(' ', '-', $post_title));
                $id = exec("wp post create '{$dirinfo->getPathname()}' --post_type=page --post_title='{$post_title}' --post_status='draft' --post_name='{$post_name}' --post_author=1 --menu_order='{$menu_order}' --porcelain");
                $legal_pages[$post_name] = ['ID' => $id, 'post_title' => $post_title];
                ++$menu_order;
            }
        }

        //Set the homepage as the front page
        if(!empty($pages[0]['ID'])){
            $io->write('<info>[INFO]</info> Setting Home Page set to WordPress static front page');
            exec('wp option set page_on_front '.$pages[0]['ID']);
            exec('wp option set show_on_front page');
        }

        //Set the posts page
        if(!empty($pages[2]['ID'])){
            $io->write('<info>[INFO]</info> Setting Blog Page set to WordPress posts page');
            exec('wp option set page_for_posts '.$pages[2]['ID']);
        }

        if(isset($legal_pages['privacy-policy']) && !empty($legal_pages['privacy-policy']['ID'])){
            $io->write('<info>[INFO]</info> Setting Privacy Policy to WordPress setting');
            exec('wp option set wp_page_for_privacy_policy '.$legal_pages['privacy-policy']['ID']);
        }

        //Setup default menu
        $io->write('<info>[INFO]</info> Setting default menus');
        exec('wp menu create "Main navigation"');
        exec('wp menu create "Footer navigation"');
        exec('wp menu create "Legal navigation"');
        exec('wp menu location assign main-navigation header_menu');
        exec('wp menu location assign footer-navigation footer_menu');
        exec('wp menu location assign legal-navigation legal_menu');

        foreach($pages as $page){
            if($page['post_status'] === 'publish' && !empty($page['ID'])){
                exec("wp menu item add-post main-navigation '{$page['ID']}'");
            }
        }

        foreach($legal_pages as $page){
            if(!empty($page['ID'])){
                exec("wp menu item add-post legal-navigation '{$page['ID']}'");
            }
        }
    }

    private static function defaultForms($io, $themeName){
        $io->write('<info>[INFO]</info> Adding default gravity form settings and forms');

        $dir = new \DirectoryIterator("www/app/themes/{$themeName}/forms");
        foreach ($dir as $dirinfo) {
            if (!$dirinfo->isDot() && $dirinfo->getExtension() === 'json') {
                $id = exec("wp gf form import '{$dirinfo->getPathname()}'");
            }
        }
    }

    /**
    * Check auth.json is ready to go
    *
    * @param Event $event
    * @return void
    */
    public static function checkACFAuth(Event $event){
        // Provides access to the current Composer instance.
        $composer = $event->getComposer();
        $io = $event->getIO();

        // Check if site has been setup previously.
        if (file_exists('auth.json')) {
            return;
        }

        $io->write('<warning>[WARNING]</warning> ACF auth.json not found');

        $content = '{
    "http-basic": {
        "connect.advancedcustomfields.com": {
            "username": "<license-code>",
            "password": "https://<domain>"
        }
    }
}';
        $htaccess = fopen('auth.json', 'w');
        fwrite($htaccess, $content);
        fclose($htaccess);

        if (file_exists('auth.json')) {
            $io->write('<info>[INFO]</info> auth.json created - please fill in the correct details');
            return;
        }
    }
}
