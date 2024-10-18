<?php

namespace JuiceBox\Core;

trait HasModuleLoop {

    public function get_modules($module_field = 'modules', $option = false)
    {
        if ($option) {
            $modules = get_field($module_field, 'option');

            if (empty($modules)) {
                return;
            }

            $modules = $modules['modules'];
        } else {
            $modules = $this->get_field($module_field);
        }

        if (empty($modules)) {
            return;
        }

        $processedModules = array();

        foreach ($modules as $index => $module) {
            if (!isset($module['acf_fc_layout'])) {
                if (WP_ENV !== 'production') {
                    echo 'Module is missing the acf_fc_layout key.';
                    var_dump($module);
                    die();
                }

                continue;
            }

            $name = $module['acf_fc_layout'];

            // Module processor namespace is PascalCase. Convert from underscore name in ACF
            $parts = explode('_', $name);
            $parts = array_map(function ($word) {
                return ucfirst($word);
            }, $parts);
            $namespace = implode('', $parts);
            $fqcn = '\\JuiceBox\\Modules\\'.$namespace.'\\Module';

            if ( class_exists($fqcn) ) {
                $moduleProcessor = new $fqcn($module, $name, $this);
                $module = $moduleProcessor->getModule();

                $module['template'] = $moduleProcessor->getTemplate();
                $module['fqcn'] = $fqcn;
                $module['index'] = $index;
                $module['name'] = $name;

                $processedModules[] = $module;
            }
        }

        return $processedModules;
    }
}
