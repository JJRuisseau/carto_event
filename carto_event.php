<?php 
/**
 * @package CartoEvent
 */
/*
Plugin Name: Carto Event
Plugin URI: http://generationfrexit.fr
Description: Permet de générer des points d'évènements sur une carte interactive
Version: 1.0.0
Author: Xadezign
Author URI: http://www.xadezign.com
License: GPLv2 or later
Text Domain: cartoevent
*/

// Si le fichier est appelé en premier, stopper tout !
defined('ABSPATH') or die("Accès impossible");

// Requiert le "Composer Autoload"
if(file_exists(dirname(__FILE__) . '/vendor/autoload.php')){
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Fonction qui démarre durant l'activation du plugin
 */
function activate_carto_event_plugin() {
    Inc\Base\Activate::activate();
}
register_activation_hook(__FILE__, 'activate_carto_event_plugin');

/**
 * Fonction qui démarre durant la désactivation du plugin
 */
function deactivate_carto_event_plugin() {
    Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_carto_event_plugin');

/**
 * Initialise toutes les classes du plugin
 */
if(class_exists('Inc\\Init')){
    Inc\Init::register_services();
}