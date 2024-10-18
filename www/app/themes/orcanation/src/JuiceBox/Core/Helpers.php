<?php
/**
 * A class of helper functions that can be reused across sites.
 *
 * Can be called in any templates file, `JuiceBox\Core\Helpers::is_dev()`
 */

namespace JuiceBox\Core;

class Helpers
{
    public static function is_ajax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
