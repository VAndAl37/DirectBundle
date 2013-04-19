<?php

namespace Ext\DirectBundle\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ext\DirectBundle\Router\ControllerResolver;

/**
 * @author Semyon Velichko <semyon@velichko.net>
 */
class ResponseFactory
{
    
    protected $request;
    protected $response;
    protected $dispatcher;
    protected $container;
    protected $config;
    protected $data;
    protected $resolver;
    
    public function __construct(Request $request, ControllerResolver $resolver, ContainerInterface $container)
    {
        $this->request = $request;
        $this->resolver = $resolver;
        $this->container = $container;
        // die(var_dump($this));
    }
    
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function createResponse(ResponseInterface $response, $data = null)
    {
        $this->response = $response;
        $this->response->setFactory($this);
        if($data !== null)
            $this->response->setContent($data);
        
        return $this->response;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getResolver()
    {
        return $this->resolver;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
}
