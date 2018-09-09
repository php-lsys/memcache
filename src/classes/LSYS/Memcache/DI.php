<?php
namespace LSYS\Memcache;
/**
 * @method \LSYS\Memcache memcache($config=null)
 */
class DI extends \LSYS\DI{
    /**
     *
     * @var string default config
     */
    public static $config = 'memcache.default';
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->memcache)&&$di->memcache(new \LSYS\DI\ShareCallback(function($config=null){
            return $config?$config:self::$config;
        },function($config=null){
            $config=\LSYS\Config\DI::get()->config($config?$config:self::$config);
            return new \LSYS\Memcache($config);
        }));
        return $di;
    }
}