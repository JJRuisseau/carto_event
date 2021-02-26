<?php
/** 
 * @package CartoGF
 */
namespace Inc\Base;

class Activate
{
    public static function activate() {
        flush_rewrite_rules();
        $default = array();

        // Activer l'option Carto.
        if(!get_option('carto_event_plugin')){
            update_option('carto_event_plugin', $default);
        }
    }
}