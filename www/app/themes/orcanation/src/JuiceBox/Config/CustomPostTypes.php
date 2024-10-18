<?php

namespace JuiceBox\Config;

use JuiceBox\PostType\Example;

class CustomPostTypes
{
    public static function register($filter = 'init', $priority = 10)
    {
        add_action($filter, function(){
            $dir = new \DirectoryIterator(get_stylesheet_directory() . '/src/JuiceBox/PostType');

            foreach ($dir as $dirinfo) {

                if (!$dirinfo->isDot() && !in_array($dirinfo->getFilename(), ['CustomPostType.php', 'Example.php']) ) {
                    $filename = $dirinfo->getFilename();

                    $filename = str_replace('.php', '', $filename);

                    $class = "\\JuiceBox\\PostType\\{$filename}";

                    $class::register();
                }
            }
        }, $priority);
    }
}
