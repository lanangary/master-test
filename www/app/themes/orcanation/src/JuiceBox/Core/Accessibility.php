<?php

namespace JuiceBox\Core;

class Accessibility {

    public function __construct() {
        add_filter('style_loader_tag', [$this, 'strip_script_types'], 10, 3);
        add_filter('script_loader_tag', [$this, 'strip_script_types'], 10, 3);
    }

  	public function strip_script_types($tag) {
        return preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $tag );
    }
}
?>
