<?php

namespace JuiceBox\Taxonomy;

use JuiceBox\PostType\CustomPostType;

abstract class CustomTaxonomy
{
    protected static $taxonomyName = 'custom_taxonomy';

    protected static $singularName = 'Custom Taxonomy';

    protected static $pluralName = 'Custom Taxonomies';

    /**
     * Arry of post types to assign the taxonomy too
     * @var array
     */
    protected static $objectType = array(

    );

    public static function register()
    {
        register_taxonomy(
            static::taxonomyName(),
            static::objectType(),
            [
                'hierarchical'      => true,
                'labels'            => array(
                    'name'              => _x( static::pluralName(), 'taxonomy general name' ),
                    'singular_name'     => _x( static::singularName(), 'taxonomy singular name' ),
                    'search_items'      => __( 'Search ' . static::pluralName() ),
                    'all_items'         => __( 'All ' . static::pluralName() ),
                    'parent_item'       => __( 'Parent ' . static::singularName() ),
                    'parent_item_colon' => __( 'Parent ' . static::singularName() . ':' ),
                    'edit_item'         => __( 'Edit ' . static::singularName() ),
                    'update_item'       => __( 'Update ' . static::singularName() ),
                    'add_new_item'      => __( 'Add New ' . static::singularName() ),
                    'new_item_name'     => __( 'New ' . static::singularName() ),
                    'menu_name'         => __( static::pluralName() ),
                    'not_found'         => __( 'No ' . static::pluralName() . ' found.' )
                ),
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'description'       => ''
            ]
        );
    }

    public static function taxonomyName()
    {
        return static::$taxonomyName;
    }

    public static function singularName()
    {
        return static::$singularName;
    }

    public static function pluralName()
    {
        return static::$pluralName;
    }

    public static function objectType()
    {
        return static::$objectType;
    }
}
