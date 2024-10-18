<?php
namespace JuiceBox\Core;

use WP_CLI;

/**
 * ACFJson
 * - Class for handling the Juicebox "Modules" setup
 *
 ***** BIG CHANGE: For greater than ACF 5.8.14 *****
 * - The sync option in the admin panel disappeared because the configuration for acf/settings/json was turned off (wasn't an issue before); but we need this to be able to sync fields (to help prevent database vs JSON and fields missing during commits)
 *      - We were loading fields on each page load by going through each module folder then including via PHP!!! (SLOW)
 * - Field group for "Modules" used to have an empty "layouts"; now it would load that JSON file empty and fields.json in each module would be ignored because it had loaded the JSON first.
 * - NEW: We save the "layouts" into the "Modules" group now for speed (otherwise we are traversing every module folder to get the fields on load!)
 * - NEW: There is now a "Sync Modules" sub menu item under ACF (or Custom Fields) to sync the "Modules" field group now.
 * - NEW: There is a WP CLI "acf-sync-modules" command to do the same; only much faster.
 */
class ACFJson
{
    protected $json_location;
    protected $module_location;
    protected $module_key = ['group_588574a592c85'];
    protected $template_dir = '__template';
    protected $sync_jb_modules;
    public const SYNC_VERSION_CUTOFF = '5.8.14';

    public function __construct()
    {
        // Set the location for json to live, your child theme can override.
        $this->json_location = apply_filters('jb/acfjson/json_location', get_stylesheet_directory() . '/acf-json');
        $this->module_location = apply_filters('jb/acfjson/module_location', get_stylesheet_directory() . '/src/JuiceBox/Modules/');

        // Equal or less than v5.8.14 we load local groups ourselves
        if(defined('ACF_VERSION') && version_compare(ACF_VERSION, ACFJson::SYNC_VERSION_CUTOFF, '<=')){
            // Stop ACF handling JSON, we are taking over
            add_filter('acf/settings/json', '__return_false');
            add_action('acf/include_fields', [$this, 'include_fields'], 1, 5);
        }else{
            // Add a new menu item to the ACF pages
            add_action('admin_menu', function(){
                $this->sync_jb_modules = add_submenu_page(
                    'edit.php?post_type=acf-field-group',
                    __( 'Sync Modules', 'acf' ),
                    __( 'Sync Modules', 'acf' ),
                    \acf_get_setting( 'capability' ),
                    'sync_jb_modules',
                    [ $this, 'sync_jb_modules' ]
                );
                add_action( 'load-' . $this->sync_jb_modules, [ $this, 'sync_jb_modules' ] );
            });
            // Add new sync WP CLI command
            if(defined('WP_CLI')){
                WP_CLI::add_command( 'acf-sync-modules', [$this, 'wpcli_sync_jb_modules'], [
                    'shortdesc' => 'Sync the modules field group for this site',
                    'longdesc' => 'Syncs the modules field group and all the layouts for this site. This will ensure the field group JSON and individual fields.json is in sync'
                ]);
            }
        }

        // Admin action related
        add_action('acf/update_field_group',        [$this, 'update_field_group'], 10, 5);
        add_action('acf/duplicate_field_group',     [$this, 'update_field_group'], 10, 5);
        add_action('acf/untrash_field_group',       [$this, 'update_field_group'], 10, 5);
        add_action('acf/trash_field_group',         [$this, 'delete_field_group'], 10, 5);
        add_action('acf/delete_field_group',        [$this, 'delete_field_group'], 10, 5);
    }

    /**
     * WP CLI command to sync the modules - handy if the browser is slow or timing out
     *
     * @param array|null $args
     * @param array|null $assoc_args
     * @return void
     */
    public function wpcli_sync_jb_modules(?array $args, ?array $assoc_args): void
    {
        $force = isset($assoc_args['force']);

        // Disabled "Local JSON" controller to prevent the .json file from being modified during import.
        \acf_update_setting( 'json', false );

        // Sync field groups and generate array of new IDs.
        $files = \acf_get_local_json_files( 'acf-field-group' );
        $all_posts = function_exists('acf_get_internal_post_type_posts') ? \acf_get_internal_post_type_posts( 'acf-field-group' ) : \acf_get_field_groups();
        foreach ( $all_posts as $post ) {
            if(in_array($post['key'], $this->module_key)){

                $modified = \acf_maybe_get( $post, 'modified' );
                $json  = json_decode( file_get_contents( $files[ $post['key'] ] ), true );
                if ( $force || ($modified && $modified > get_post_modified_time( 'U', true, $post['ID'] )) || count($json['fields'][0]['layouts']) === 0 ) {
                    $json  = json_decode( file_get_contents( $files[ $post['key'] ] ), true );
                    $json['fields'][0]['layouts'] = $this->include_module_fields($post['key']);
                    $json['ID'] = $post['ID'];
                    $result = function_exists('acf_import_internal_post_type') ? \acf_import_internal_post_type( $json, 'acf-field-group' ) : \acf_import_field_group( $json );
                    $new_ids[] = $result['ID'];

                    $this->acf_write_json_field_group($json);

                    WP_CLI::success('Modules successfully synced');
                    return;
                }else{
                    WP_CLI::success('Modules are already synced, if you want to sync anyway run the same command with --force');
                    return;
                }
            }
        }

        WP_CLI::warning('No modules configurations found');
    }

    /**
     * Allows us to check & sync the "Modules" group using the 'fields.json' in each module folder
     * - This was to allow ACF > 5.8.14 to use the fields.json instead of the group JSON which has an empty "layouts" configuration
     * - It still checks the "modified" date to judge whether the fields are out of sync.
     *
     * @return void
     */
    public function sync_jb_modules(): void
    {
        // Only run the sync after user triggers it
        if(isset($_GET['run'])){
            // Disabled "Local JSON" controller to prevent the .json file from being modified during import.
            \acf_update_setting( 'json', false );

            // Sync field groups and generate array of new IDs.
            $files = \acf_get_local_json_files( 'acf-field-group' );
            $all_posts = function_exists('acf_get_internal_post_type_posts') ? \acf_get_internal_post_type_posts( 'acf-field-group' ) : \acf_get_field_groups();
            foreach ( $all_posts as $post ) {
                if(in_array($post['key'], $this->module_key)){

                    $modified = \acf_maybe_get( $post, 'modified' );
                    $json  = json_decode( file_get_contents( $files[ $post['key'] ] ), true );
                    if ( ($modified && $modified > get_post_modified_time( 'U', true, $post['ID'] )) || count($json['fields'][0]['layouts']) === 0 ) {
                        $json  = json_decode( file_get_contents( $files[ $post['key'] ] ), true );
                        $json['fields'][0]['layouts'] = $this->include_module_fields($post['key']);
                        $json['ID'] = $post['ID'];
                        $result = function_exists('acf_import_internal_post_type') ? \acf_import_internal_post_type( $json, 'acf-field-group' ) : \acf_import_field_group( $json );
                        $new_ids[] = $result['ID'];

                        // Redirect.
                        wp_safe_redirect( 'edit.php?post_type=acf-field-group&acfsynccomplete=' . implode( ',', $new_ids ) );
                        exit;
                    }else{
                        \acf_add_admin_notice( 'Modules are already synced', 'success' );
                        return;
                    }
                }
            }

            wp_safe_redirect( 'edit.php?post_type=acf-field-group' );
            exit;

        // Check to see if we need to sync
        }else{
            $files = \acf_get_local_json_files( 'acf-field-group' );
            $all_posts = function_exists('acf_get_internal_post_type_posts') ? \acf_get_internal_post_type_posts( 'acf-field-group' ) : \acf_get_field_groups();
            foreach ( $all_posts as $post ) {
                if(in_array($post['key'], $this->module_key)){

                    $modified = \acf_maybe_get( $post, 'modified' );
                    $json  = json_decode( file_get_contents( $files[ $post['key'] ] ), true );

                    // File modified or the layouts is empty
                    if ( ($modified && $modified > get_post_modified_time( 'U', true, $post['ID'] )) || count($json['fields'][0]['layouts']) === 0 ) {
                        \acf_add_admin_notice( '<h2>Modules are out of sync</h2><a href="'.admin_url('edit.php?post_type=acf-field-group&page=sync_jb_modules&run=1').'">Sync Now</a> or use the faster method <code>acf-sync-modules</code> via WP CLI', 'info' );
                    }else{
                        \acf_add_admin_notice( 'Modules are already synced', 'success' );
                        return;
                    }
                }
            }
        }
    }


    /**
     * Saves the JSON for the provided field group
     *
     * @param array $field_group
     * @return void
     */
    public function update_field_group( array $field_group ): void
    {
        // Get fields
        $field_group['fields'] = \acf_get_fields( $field_group );

        // Save file
        $this->acf_write_json_field_group( $field_group );
    }

    /**
     * Delete's the JSON for the provided field group
     *
     * @param array $field_group
     * @return void
     */
    public function delete_field_group( array $field_group ): void
    {
        // WP appends '__trashed' to end of 'key' (post_name)
        $field_group['key'] = str_replace('__trashed', '', $field_group['key']);

        // delete
        \acf_delete_json_field_group( $field_group['key'] );
    }

    /**
     * Manually includes all the JSON configurations via PHP as a local group
     * - For the case of modules, includes all their configurations in fields.json for each folder
     *
     * @return void
     */
    public function include_fields(): void
    {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        // check that path exists
        $path = untrailingslashit($this->json_location);
        if( !file_exists( $path ) ) {
            return;
        }

        $dir = opendir( $path );
        while(false !== ( $file = readdir($dir)) ) {

            // only json files
            if( strpos($file, '.json') === false ) {
                continue;
            }

            // read json
            $json = file_get_contents("{$path}/{$file}");

            // validate json
            if( empty($json) ) {
                continue;
            }

            // decode
            $json = json_decode($json, true);

            // Load module fields
            if ( in_array($json['key'], $this->module_key) ) {
                $json['fields'][0]['layouts'] = $this->include_module_fields($json['key']);

                // Turn off the standard "sync" option for our modules
                if(defined('ACF_VERSION') && version_compare(ACF_VERSION, ACFJson::SYNC_VERSION_CUTOFF, '>')){
                    $json['private'] = true;
                }
            }

            // add local
            $json['local'] = 'json';

            // add field group
            \acf_add_local_field_group( $json );
        }
    }

    /**
     * Error notice for ACF folder not being writable
     *
     * @return void
     */
    public function admin_notice_error__acf_folder(): void
    {
        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', 'ACF JSON folder is not writable.' );
    }

    /**
     * Error notice for a missing name for a layout
     *
     * @return void
     */
    public function admin_notice_error__layout_name(): void
    {
        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', 'You have not specified a name for one of your modules.' );
    }

    /**
     * Error notification for an errored module folder
     *
     * @return void
     */
    public function admin_notice_error__module_folder(): void
    {
        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', 'One of your modules folder structure or permissions are incorrect.' );
    }

    /**
     * Saves the ACF field group - and if it matches the module keys it saves the module fields.json
     *
     * @param array $field_group
     * @return bool
     */
    private function acf_write_json_field_group( array $field_group ): bool
    {
        $path = untrailingslashit($this->json_location);
        $file = $field_group['key'] . '.json';

        // Bail early if dir does not exist
        if( !is_writable($path) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_error__acf_folder'] );
            return false;
        }

        // Prepare for export
        $id = \acf_extract_var( $field_group, 'ID' );
        $field_group = \acf_prepare_field_group_for_export( $field_group );

        // If we are processing modules...
        if ( in_array($field_group['key'], $this->module_key) ) {
            // Extract layouts
            $layouts = $field_group['fields'][0]['layouts'];
            
            //sort layouts by name
            usort($layouts, function ($a, $b) {
                if ($a['label'] == $b['label']) {
                    return 0;
                }
                return ($a['label'] < $b['label']) ? -1 : 1;
            });

            $field_group['fields'][0]['layouts'] = $layouts;

            //sort layouts by name
            usort($layouts, function ($a, $b) {
                if ($a['label'] == $b['label']) {
                    return 0;
                }
                return ($a['label'] < $b['label']) ? -1 : 1;
            });

            $field_group['fields'][0]['layouts'] = $layouts;

            // For > ACF 5.8.14
            // - We save the "layouts" into the "Modules" group now for speed (otherwise we are traversing every module folder to get the fields on load!)
            if(defined('ACF_VERSION') && version_compare(ACF_VERSION, ACFJson::SYNC_VERSION_CUTOFF, '<=')){
                // Set layouts to empty array
                $field_group['fields'][0]['layouts'] = [];
            }else{
                $field_group['private'] = true;
            }

            // Saves the fields.json in each module
            $this->save_module_json($layouts, $field_group['key']);
        }

        // Add modified time
        $field_group['modified'] = get_post_modified_time('U', true, $id, true);

        // Write file
        $f = fopen("{$path}/{$file}", 'w');
        fwrite($f, \acf_json_encode( $field_group ));
        fclose($f);

        return true;
    }

    /**
     * Saves a fields.json for each module based on the layouts for the provided group
     *
     * @param array $layouts - the array of fields assigned to the repeater group
     * @param string $id - the group
     * @return bool
     */
    private function save_module_json(array $layouts, string $id): bool
    {
        $path = untrailingslashit( $this->module_location );
        if(!file_exists($path) || count($layouts) === 0){
            return false;
        }

        foreach ( $layouts as $layout ) {
            if ( $layout['name'] === '' ) {
                add_action( 'admin_notices', [$this, 'admin_notice_error__layout_name'] );
                continue;
            }

            $layout['group'] = $id;
            $namespace = $this->to_pascal_case($layout['name']);
            $file = "{$namespace}/fields.json";

            // Create the folder and copy template files in
            if( !is_writable("{$path}/{$namespace}/") ) {
                $this->setup_folder_structure($path, $namespace);
            }

            $f = fopen("{$path}/{$file}", 'w');
            fwrite($f, \acf_json_encode( $layout ));
            fclose($f);
        }
        return true;
    }

    /**
     * Sets up a new module folder based on a template
     *
     * @param string $path - the path of the module
     * @param string $namespace - the name of the folder containing the module
     * @return void
     */
    private function setup_folder_structure(string $path, string $namespace): void
    {
        // Create folder
        mkdir("{$path}/{$namespace}/", 0775, true);

        $dir = new \DirectoryIterator("{$path}/{$this->template_dir}");

        $replace = [
            '__MODULENAME__' => $namespace
        ];

        foreach ($dir as $fileinfo) {
            // Not .  or  ..
            if (!$fileinfo->isDot()) {
                $fileName = "{$path}/{$namespace}/".$fileinfo->getFilename();

                $file = file_get_contents( $fileinfo->getPathname() );
                $file = str_replace(array_keys($replace), array_values($replace), $file);

                file_put_contents($fileName, $file);
                chmod($fileName, 0664);
            }
        }
    }

    /**
     * Grab the layout field configurations for the provided group key
     *
     * @param string|null $id key of the ACF group to compare
     * @return void
     */
    private function include_module_fields(?string $id): ?array
    {
        // Remove trailing slash
        $path = untrailingslashit($this->module_location);

        // Check that path exists
        if( !file_exists( $path ) ) {
            return null;
        }

        $dir = opendir( $path );
        $return = [];

        // Loop through the directories
        while(false !== ( $folder = readdir($dir)) ) {
            // Check we've got some field configurations
            if ( !is_dir("{$path}/{$folder}") || !file_exists("{$path}/{$folder}/fields.json") || substr($folder, 0, 2) === '__' ) {
                continue;
            }

            // Read json
            $json = file_get_contents("{$path}/{$folder}/fields.json");

            // Validate json
            if( empty($json) ) {
                continue;
            }

            $json = json_decode($json, true);

            // Double check we've got the correct group
            if ( isset($json['group']) && $json['group'] != $id ) {
                continue;
            }
            unset($json['group']);

            $return[] = $json;
        }

        return $return;
    }

    /**
     * Convert string to pascal case
     *
     * @param string|null $string
     * @param string $delimeter
     * @return string|null
     */
    private function to_pascal_case(?string $string, string $delimeter = '_'): ?string
    {
        $parts = explode($delimeter, $string);

        $parts = array_map(function ($word) {
            return ucfirst($word);
        }, $parts);

        return implode('', $parts);
    }
}
