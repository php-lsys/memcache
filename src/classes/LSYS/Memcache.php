<?php
/**
 * lsys service
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS;
class Memcache extends \Memcache{
    /**
     * @var Config
     */
    protected $_config;
    /**
     * The default configuration for the memcached server
     *
     * @var array
     */
    protected $_default_config = array();
    // OK SERVER
    protected $_servers = array();
    protected $_is_config=false;
    public function __construct(Config $config){
        $this->_config=$config;
    }
    public function configServers(){
        if ($this->_is_config)return $this;
        $config=$this->_config;
        $servers =(array)$config->get("servers",array());
        if ( count($servers)==0 )
        {
            throw new Exception('No Memcache servers defined in configuration');
        }
        $this->_default_config=array(
            'host'             => 'localhost',
            'port'             => 11211,
            'persistent'       => FALSE,
            'weight'           => 1,
            'timeout'          => 1,
            'retry_interval'   => 15,
            'status'           => TRUE,
            'instant_death'	   => TRUE,
            'failure_callback' => array($this, '_failedRequest'),
        );
        $this->_servers=$servers;
        // Add the memcache servers to the pool
        foreach ($servers as $server)
        {
            // Merge the defined config with defaults
            $server += $this->_default_config;
            if ( ! $this->addServer($server['host'], $server['port'], $server['persistent'], $server['weight'], $server['timeout'], $server['retry_interval'], $server['status'], $server['failure_callback']))
            {
                throw new Exception(strtr("Memcache could not connect to host :host using port :port",array(":host"=>$server['host'],':port'=>$server['port'])));
            }
        }
        $this->_is_config=true;
        return $this;
    }
    /**
     * Callback method for Memcache::failure_callback to use if any Memcache call
     * on a particular server fails. This method switches off that instance of the
     * server if the configuration setting `instant_death` is set to `TRUE`.
     *
     * @param   string   $hostname
     * @param   integer  $port
     * @return  void|boolean
     * @since   3.0.8
     */
    public function _failedRequest($hostname, $port)
    {
        if ( ! $this->_config->get('instant_death',true)) return;
        // Setup non-existent host
        $host = FALSE;
        // Get host settings from configuration
        foreach ($this->_servers as $k=>$server)
        {
            // Merge the defaults, since they won't always be set
            $server += $this->_default_config;
            // We're looking at the failed server
            if ($hostname == $server['host'] and $port == $server['port'])
            {
                unset($this->_servers[$k]);
                // Server to disable, since it failed
                $host = $server;
                continue;
            }
        }
        if (count($this->_servers)==0){
            throw new Exception('Memcache servers all shutdown');
        }
        if ( ! $host) return;
        else {
            return $this->setServerParams(
                $host['host'],
                $host['port'],
                $host['timeout'],
                $host['retry_interval'],
                FALSE, // Server is offline
                array($this, '_failedRequest')
            );
        }
    }
}