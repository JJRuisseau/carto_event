<?php
/** 
 * @package CartoEvent
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

class CartoEventCallbacks extends BaseController 
{
    public function shortcodePage() {
        return require_once("$this->plugin_path/templates/carto_event.php");
    }
}