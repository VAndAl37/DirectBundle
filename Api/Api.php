<?php
namespace Ext\DirectBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Api is the ExtDirect Api class.
 *
 * It provide the ExtDirect Api descriptor of exposed Controllers and methods.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 * @author Semyon Velichko <semyon@velichko.net>
 */
class Api extends \ArrayObject
{

    /**
     * The ExtDirect JSON API description.
     * 
     * @var array
     */
    protected $api = array();// = array('actions' => array());
    
    protected $config;

    protected $container;

    protected $reader;
    
    // const Bundle_Action_Regex = '/^([\w]+)Bundle:([\w]+):([\w]+)$/i';
    // const Service_Regex = '/^([\w]+):([\w]+)$/i';

    /**
     * Initialize the API.
     */
    public function __construct(ContainerInterface $container, $reader)//, $config
    {
        $this->container = $container;
        $this->reader = $reader;

        if ($container->get('kernel')->isDebug()) {
            $this->createApi();
        } else {
            $this->getApiFromCache();
        }
    }

    public function getApi()
    {
        return $this->api;
    }

    public function is_remote($call)
    {
        $api = $this->api;
        return array_key_exists($call->getAction(), $api) &&
            array_key_exists($call->getMethod(), $api[$call->getAction()]);
    }

    /**
     * Return the API in JSON format.
     *
     * @return string JSON API description
     */
    public function toArray()
    {    
        $api = array();
        foreach ($this->api as $name => $action) {
            $api[$name] = $action->toArray();
        }
        return $api;
    }

    /**
     * Return the API in JSON format.
     *
     * @return string JSON API description
     */
    public function  __toString()
    {  
        return json_encode(array_merge(
            $this->config['basic'],
            array('actions' => $this->toArray())
        ));
    }

    /**
     * Create the ExtDirect API based on config.yml or direct.yml files.
     *
     * @return string JSON description of Direct API
     */
    protected function createApi()
    {
        $bundles = $this->getControllers();
        $api = array();

        foreach ($bundles as $bundle => $controllers ) {
            $bundleShortName = str_replace('Bundle', '', $bundle);
            
            foreach ($controllers as $controller) {
                $action = new ControllerApi($this->reader, $controller);
                                
                if ($action->isExposed()) {
                    $api[$bundleShortName."_".$action->getActionName()] = $action;
                }
            }
        }

        $this->api = $api;


        // $api = array();
        
        // foreach($this->config['router']['rules'] as $rule) {
        //     if(preg_match($this::Bundle_Action_Regex, $rule['defaults']['_controller'], $match)) {
        //         list($all, $shortBundleName, $controllerName, $methodName) = $match;
        //         $key = sprintf('%s_%s', $shortBundleName, $controllerName);
        //     } elseif(preg_match($this::Service_Regex, $rule['defaults']['_controller'], $match)) {
        //         list($all, $key, $methodName) = $match;
        //     } else {
        //         throw new \InvalidArgumentException();
        //     }

        //     if(!array_key_exists($key, $api) or !is_array($api[$key]))
        //         $api[$key] = array();
                
        //     $methodParams = array('name' => $methodName, 'len' => (integer)$rule['defaults']['params']);
            
        //     if($rule['defaults']['form'])
        //         $methodParams['formHandler'] = true;
            
        //     $api[$key][] = $methodParams;
        // }
        // die('<pre>'.var_export(array($actions, $api), true).'</pre>');
        // $this->api['actions'] = $api;
    }

    /**
     * Return the cached ExtDirect API.
     *
     * @return string JSON description of Direct API
     */
    protected function getApiFromCache()
    {
        //@todo: implement the cache mechanism
        return $this->createApi();
    }

    /**
     * Get all controllers from all bundles.
     *
     * @return array Controllers list
     */
    protected function getControllers()
    {
        $controllers = array();
        $finder = new ControllerFinder();
        
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            $found = $finder->getControllers($bundle);
            if (!empty ($found)) {
                $controllers[$bundle->getName()] = $found;
            }
        }

        return $controllers;
    }

    public function setConfig($config)
    {
        $this->config = array_merge_recursive($config, array('basic' => array('url' => $this->container->get('router')->generate('ExtDirectBundle_route'))));
    }
    
    public function getConfig()
    {
        return $this->config;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->api[] = $value;
        } else {
            $this->api[$offset] = $value;
        }
    }
    
    public function offsetGet($offset)
    {
        return isset($this->api[$offset]) ? $this->api[$offset] : null;
    }
}
