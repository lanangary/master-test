<?php
//Include composer autoloader
include ABSPATH . "../../vendor/autoload.php";

use Timber\Timber;

use JuiceBox\Core\Site;
use JuiceBox\Core\Admin;
use JuiceBox\Core\GravityForms;
use JuiceBox\Core\ACFJson;
use JuiceBox\Core\DefaultContent;
use JuiceBox\Core\Accessibility;
use JuiceBox\Core\SEO;
//use JuiceBox\Core\Agile;

use JuiceBox\Config\Shortcodes;
use JuiceBox\Config\ThemeSupport;
use JuiceBox\Config\CustomPostTypes;
use JuiceBox\Config\CustomTaxonomies;
use JuiceBox\Config\Menus;
use JuiceBox\Config\Assets;

new Timber();

/**
 * ------------------
 * Core
 * ------------------
 */
if(class_exists('\JuiceBox\Core\Site')){

    // Site Config
    new Site();
    new Admin();
    new ACFJson();
    new Accessibility();
    new SEO();
    new DefaultContent();
    new GravityForms();
    //new Agile();
}

/**
 * ------------------
 * Config
 * ------------------
 */
if(class_exists('\JuiceBox\Config\Assets')){

    // Register Custom Taxonomies
    CustomTaxonomies::register();

    // Register CPT
    CustomPostTypes::register();

    // Register WordPress menus
    Menus::register();

    // Load JS/CSS
    Assets::load();

    // Shortcodes.
    Shortcodes::register();

    // Register theme support.
    ThemeSupport::register();
}
