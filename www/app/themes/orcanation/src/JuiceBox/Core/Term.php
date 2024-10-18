<?php

namespace JuiceBox\Core;

use \Timber\Term as TimberTerm;

class Term extends TimberTerm {

    use HasModuleLoop;

    public $PostClass = '\\JuiceBox\\Core\\Post';
    public $TermClass = '\\JuiceBox\\Core\\Term';
    public $ImageClass = '\\JuiceBox\\Core\\Image';

    public function title()
    {
        return $this->name;
    }

    public function content()
    {
        return $this->description;
    }

    public function post_type()
    {
        return $this->object_type;
    }

    public function get_taxonomy()
    {
        return get_taxonomy( $this->taxonomy );
    }

    public function get_field($selector)
    {
        return get_field($selector, "{$this->taxonomy}_{$this->term_id}");
    }

    public function get_thumbnail($fallback = false)
    {
        $img = $this->get_field('featured_image');

        if ( $img !== null ) {
            return new $this->ImageClass($img);
        }

        if( $fallback ) {
            $default = get_field($fallback, 'option');

            if ( ! empty($default) ) {
                return new $this->ImageClass($default);
            }
        }

        return null;
    }

    /**
     * @return null|TimberImage
     */
    public function thumbnail($fallback = false)
    {
        return $this->get_thumbnail($fallback);
    }
}
