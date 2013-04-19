<?php

namespace Ext\DirectBundle\Response;

/**
 * @author Semyon Velichko <semyon@velichko.net>
 */
class Exception extends Response implements ResponseInterface
{
    
    protected $exception;
    
    public function __construct(\Exception $e)
    {
        $this->setException($e);
    }
    
    public function setException(\Exception $e)
    {
        $this->exception = $e;
        return $this;
    }
    
    public function getException()
    {
        return $this->exception;
    }
    
    public function formatResponse(array $data)
    {
        return array('msg' => sprintf("exception '%s' with message '%s'", get_class($this->getException()), $this->getException()->getMessage()),
                     'where' => sprintf('in %s: %d', $this->getException()->getFile(), $this->getException()->getLine()));
    }
}
