<?php
namespace Ext\DirectBundle\Router;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Ext\DirectBundle\Response\Exception as ExceptionResponse;
use Ext\DirectBundle\Response\Error as ErrorResponse;
use Ext\DirectBundle\Response\Response
;
/**
 * Router is the ExtDirect Router class.
 *
 * It provide the ExtDirect Router mechanism.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 * @author Semyon Velichko <semyon@velichko.net>
 */
class Router
{
    /**
     * The ExtDirect Request object.
     * 
     * @var Ext\DirectBundle\Request
     */
    protected $request;
    
    /**
     * The ExtDirect Response object.
     * 
     * @var Ext\DirectBundle\Response
     */
    protected $response;
    
    /**
     * The application container.
     * 
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;
    
    /**
     * Initialize the router object.
     * 
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->request = new Request($container->get('request'), $container->get('ext_direct.api'));
        $this->resolver = $container->get('ext_direct.controller_resolver');
    }

    /**
     * Do the ExtDirect routing processing.
     *
     * @return JSON
     */
    public function route()
    {
        $batch = array();
        foreach ($this->request->getCalls() as $call) {
            $batch[] = $this->dispatch($call);
        }
        return $batch;//json_encode($batch);
    }

    /**
     * Dispatch a remote method call.
     * 
     * @param  Ext\DirectBundle\Router\Call $call
     * @return Mixed
     */
    private function dispatch($call)
    {
        // $this->resolver = $this->container->get('ext_direct.controller_resolver2');
        $request = $this->resolver->getRequestFromCall($call);
        $controller = $this->resolver->getController($request);
        
        // die(var_dump($controller));


        // $controller = $this->resolver->getControllerFromCall($call);
        // $request = $this->container->get('request');

        if (!is_callable($controller)) {
            throw new NotFoundHttpException('Unable to find the controller for action "%s". Maybe you forgot to add the matching route in your routing configuration?', $call->getAction());
        }
        
        $arguments = $this->resolver->getArguments($request, $controller);

        if(in_array($this->container->get('kernel')->getEnvironment(), array('dev', 'test')))
        {
            try {
                $result = call_user_func_array($controller, $arguments);
            }
            
            catch (\Exception $e) {
                $result = new ExceptionResponse($e);
                $call->setType('exception');
            }
        } else {
            try {
                $result = call_user_func_array($controller, $arguments);
            }
            
            // catch (NotFoundHttpException $e) {
            //     $result = array(
            //         'success' => false,
            //         'exception' => true,
            //         'code' => $e->getStatusCode(),
            //         'msg' => $e->getMessage()
            //     );
            //     $call->setType('exception');
            // }
            catch (AccessDeniedException $e)
            {
                $errors = array();
                $error = array(
                    'message'=>'Недостаточно прав для выполнения операции.'
                );
                array_push($errors, $error);
                
                $result = $this->container->get('ext_direct')
                    ->createResponse(new ErrorResponse(), $errors);
            }
            
            catch (\Exception $e) {
                throw new Symfony\Component\HttpKernel\Exception\HttpException(404, "Some description");
            }
        }

        // $api = $this->container->get('ext_direct.api');
        
        // $result->setConfig($api[$call->getAction()][$call->getMethod()]);
        // $call->setApi($api);
        return $call->getResponse($result);
    }
}
