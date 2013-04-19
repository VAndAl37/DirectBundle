<?php
namespace Ext\DirectBundle\Api;

use Symfony\Component\DependencyInjection\Loader\FileLoader;

use Doctrine\Common\Annotations\Reader;

// use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ControllerApi encapsulate methods to get the Controller exposed Api.
 *
 * @author Otavio Fernandes <otabio@neton.com.br>
 */
class ControllerApi extends \ArrayObject //implements \IteratorAggregate, \ArrayAccess
{
    /**
     * Annotation reader.
     * 
     * @var Reader
     */
    protected $reader;

    /**
     * Store the controller reflection object.
     * 
     * @var \Reflection
     */
    protected $reflection;

    /**
     * The controller ExtDirect api.
     * 
     * @var array
     */
    protected $api;

    /**
     * Initialize the object.
     * 
     * @param \Doctrine\Common\Annotations\Reader $reader
     * @param string $controller
     */
    public function __construct(Reader $reader, $controller)
    {
        try {
            $this->reflection = new \ReflectionClass($controller);
        } catch (Exception $e) {
            // @todo: throw an exception
        }

        $this->reader = $reader;
        $this->api = $this->createApi();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->api);
    }

    /**
     * Check if the controller has any method exposed.
     *
     * @return Boolean true if has exposed, otherwise return false
     */
    public function isExposed()
    {        
        return (null != $this->api) ? true : false;
    }

    /**
     * Return the api.
     * 
     * @return array
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Return the name of exposed direct Action.
     * 
     * @return string
     */
    public function getActionName()
    {        
        return str_replace('Controller','',$this->reflection->getShortName());
    }
    
    /**
     * Try create the controller api.
     *
     * @return array
     */
    protected function createApi()
    {
        $api = null;
        
        // get public methods from controller
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $mApi = $this->getMethodApi($method);

            if ($mApi) {
                $methodName = str_replace('Action','',$method->getName());
                $api[$methodName] = $mApi;
            }
        }

        return $api;
    }

    /**
     * Return the api of method.
     *
     * @param \ReflectionMethod $method
     *
     * @return mixed (array/boolean)
     */
    private function getMethodApi($method)
    {
        $api = false;
        
        if (null !== ($remote = $this->reader->getMethodAnnotation($method, 'Ext\DirectBundle\Annotation\Remote'))) {

            $api['len'] = $method->getNumberOfParameters();
            $api['reader'] = $remote->toArray();

            if (null !== ($form = $this->reader->getMethodAnnotation($method, 'Ext\DirectBundle\Annotation\Form'))) {
                $api['formHandler'] = true;
            }
        }

        return $api;
    }

    public function toArray()
    {   
        $api = array();
        foreach ($this->api as $name => $method) {
            $m = array(
                'name' => $name,
                'len' => $method['len']
            );
            
            if(isset($method['formHandler'])) $m['formHandler'] = true;
            $api[] = $m;
        }

        return $api;
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

    // public function offsetExists($offset) {
    //     return isset($this->api[$offset]);
    // }
    // public function offsetUnset($offset) {
    //     unset($this->api[$offset]);
    // }
}