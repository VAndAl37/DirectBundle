<?php

namespace Ext\DirectBundle\Controller;
use Ext\DirectBundle\Api\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


// use Ext\DirectBundle\Tests\Binder as Test;
use Ext\DirectBundle\Router\Router;
// use Ext\DirectBundle\Response\Basic;

use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @author Semyon Velichko <semyon@velichko.net>
 */
class DirectController extends Controller
{
    
    private $config = array();
    
    public function __construct(ContainerInterface $container) {
            $this->container = $container;
            // $this->response = new HttpFoundation\Response();
            // $this->response->headers->set('Content-Type', 'text/html');
    }

    /**
     * Generate the ExtDirect API.
     * 
     * @return HttpFoundation\Response 
     */
    public function getApi()
    {
        // get the api object
        $api = $this->get('ext_direct.api');

        $response = new HttpFoundation\Response(sprintf('Ext.ns("%1$s"); %1$s.REMOTING_API = %2$s;', $this->config['basic']['namespace'], $api));

        $response->headers->set('Content-Type', 'text/javascript');
        return $response;
    }
    
    /**
     * Route the ExtDirect calls.
     *
     * @Secure(roles="ROLE_USER")
     * 
     * @param HttpFoundation\Request
     * @return HttpFoundation\Response
     */
    public function route(HttpFoundation\Request $request)
    {
        // throw new \Exception('ERROR !!!!!!!!!!!!!!!!!!!!!!!!');
        // return new HttpFoundation\Response('An error occurred', 404);
        // instantiate the router object
        $router = new Router($this->container);
        $response = new HttpFoundation\JsonResponse($router->route());

        // $response->setContent($router->route());
        // $this->response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function setConfig($config) {
        $this->config = $config;
    }
   
    public function getConfig()
    {
        return $this->config;
    }
}
