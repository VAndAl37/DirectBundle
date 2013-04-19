<?php
namespace Ext\DirectBundle\Router;

use Ext\DirectBundle\Response\ResponseInterface;
use Ext\DirectBundle\Response\Exception as ExceptionResponse;
use Ext\DirectBundle\Exception\InvalidJsonException;
/**
 * Call encapsule an single ExtDirect call.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 * @author Semyon Velichko <semyon@velichko.net>
 */
class Call
{
    /**
     * The ExtDirect action called. With reference to Bundle via underscore '_'.
     * 
     * @var string
     */
    protected $action;

    /**
     * The ExtDirect method called.
     * 
     * @var string
     */
    protected $method;

    /**
     * The ExtDirect request type.
     * 
     * @var string
     */
    protected $type = 'rpc';

    /**
     * The ExtDirect transaction id.
     * 
     * @var integer
     */
    protected $tid;

    /**
     * The ExtDirect call params.
     * 
     * @var array
     */
    protected $data;

    /**
     * The ExtDirect request type. Where values in ('form','single').
     * 
     * @var string
     */
    protected $callType;

    /**
     * Initialize an ExtDirect call.
     * 
     * @param array  $call
     * @param string $type
     */
    public function __construct($call, Request $request)
    {
        // die(var_dump(array($call, $request)));
        $this->request = $request;
        $this->initialize($call);
    }
    
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the requested action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the requested method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the request method params.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set the request type.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the request type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function setApi($api)
    {
        $this->api = $api[$this->action][$this->method];
    }

    /**
     * Return a result wrapper to ExtDirect method call.
     * 
     * @param  array $result
     * @return array
     */
    public function getResponse($result)
    {
        $return = 
            array('type' => $this->type,
                  'tid' => $this->tid,
                  'action' => $this->action,
                  'method' => $this->method);
        
        if('exception' === $this->type && $result instanceof ExceptionResponse)
            return array_merge($result->extract(), $return);

        if($result instanceof ResponseInterface)
        {
            $result->setConfig($this->api);
            $return['result'] = $result->extract();
        } else {
            $return['result'] = $result;
        }
        
        return $return;
    }
    
    /**
     * Initialize the call properties from a single call.
     * 
     * @param array $call
     */
    public function initialize($call)
    {
        foreach(array('action', 'method', 'type', 'tid') as $key)
        {
            if(!isset($call[$key]))
                throw new InvalidJsonException(sprintf('%s key does not exist', $key));
            
            $this->$key = $call[$key];
        }
        
        $this->data = array();
        
        if(is_array($call['data']) && !empty($call['data']))
            $this->data = array_shift($call['data']);
    }
}
