<?php

namespace JuiceBox\Config;

use JuiceBox\Taxonomy\Example;

class CustomTaxonomies
{
    public static function register($filter = 'init', $priority = 10)
    {
        add_action($filter, function(){
            $dir = new \DirectoryIterator(get_stylesheet_directory() . '/src/JuiceBox/Taxonomy');

            foreach ($dir as $dirinfo) {

                if (!$dirinfo->isDot() && !in_array($dirinfo->getFilename(), ['CustomTaxonomy.php', 'Example.php']) ) {
                    $filename = $dirinfo->getFilename();

                    $filename = str_replace('.php', '', $filename);

                    $class = "\\JuiceBox\\Taxonomy\\{$filename}";

                    $class::register();
                }
            }
        }, $priority);
    }
}
