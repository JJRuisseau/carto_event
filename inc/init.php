<?php
/** 
 * @package CartoEvent
 */
namespace Inc;

final class Init
{
    /**
     * Stocke toutes les classes dans un tableau
     * @return array Toutes les classes
     */
    public static function get_services() 
    {
        return [
            Base\CartoEventController::class
        ];
    }

    /**
     * Boucle à travers les classes, l'initialise, 
     * et appelle la fonction "register()" si elle existe.
     * @return
     */
    public static function register_services() {
        foreach(self::get_services() as $class){
            $service = self::instantiate($class);
            if(method_exists($service, 'register')){
                $service->register();
                
            }
        }
    }
    
    /**
     * Initialise la classe
     * @param class $class class depuis le tableau
     * @return class instance nouvelle instantiation de la classe
     */
    private static function instantiate($class)
    {
        $service = new $class();
        return $service;
    }
}
