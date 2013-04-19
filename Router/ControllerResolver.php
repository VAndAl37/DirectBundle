<?php
namespace Ext\DirectBundle\Router;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ControllerResolver implements ControllerResolverInterface
{
    protected $container;

    protected $resolver;

    public function __construct(ContainerInterface $container, ControllerResolverInterface $resolver)
    {
        $this->container = $container;
        $this->resolver = $resolver;
    }

    // public function setResolver(ControllerResolverInterface $resolver)
    // {
    //     $this->resolver = $resolver;
    // }

    public function getController(HttpRequest $request)
    {
        return $this->resolver->getController($request);
        // return call_user_func_array(array($this->resolver, __FUNCTION__), func_get_args());
    }

    public function getArguments(HttpRequest $request, $controller)
    {
        return $this->resolver->getArguments($request, $controller);
        // return call_user_func_array(array($this->resolver, __FUNCTION__), func_get_args());
    }

    public function getRequestFromCall(Call $call)
    {
        $explodeResult = explode('_', $call->getAction());
        
        if(count($explodeResult) <> 2)
        {
            $fullPath = sprintf('%1$s:%2$s', $call->getAction(), $call->getMethod());
        } else {
            list($bundle, $controller) = $explodeResult;
            $fullPath = sprintf('%1$sBundle:%2$s:%3$s', $bundle, $controller, $call->getMethod());
        }

        // $api = $this->container->get('ext_direct.api');

        // if(!$api->is_remote($call)) {
        //     throw new \BadMethodCallException(sprintf('%1$s does not configured as remote.', $fullPath));
        // }

        $attributes = array_merge($call->getData(), array(
            '_controller' => $fullPath
        ));

        return $this->container->get('request')->duplicate(null, null, $attributes);
    }

    // public function __call($name, $arguments = array())
    // {
    //     return call_user_func_array(array($this->resolver, $name), func_get_args());
    // }

    // public function __set($name, $value)
    // {
    //     $this->resolver->{$name} = $value;
    // }

    // public function __get($name)
    // {
    //     return $this->resolver->{$name};
    // }
}