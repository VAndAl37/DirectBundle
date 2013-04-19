<?php
namespace Ext\DirectBundle\Router;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Ext\DirectBundle\Exception\InvalidJsonException;
use Ext\DirectBundle\Api\Api;

/**
 * Request encapsule the ExtDirect request call.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 */
class Request
{
    /**
     * The Symfony request object taked by DirectBundle controller.
     * 
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * The HTTP_RAW_POST_DATA if the Direct call is a batch call.
     * 
     * @var JSON
     */
    protected $rawPost;
    
    /**
     * The $_POST data if the Direct Call is a form call.
     * 
     * @var array
     */
    protected $post;

    /**
     * Store the Direct Call type. Where values in ('form','batch').
     * 
     * @var string
     */
    protected $callType;

    /**
     * Store the Direct calls. Only 1 if it a form call or 1.* if it a
     * batch call.
     * 
     * @var array
     */
    protected $calls = null;

    /**
     * Store the $_FILES if it a form call.
     * 
     * @var array
     */
    protected $files = array();

    protected $api;

    /**
     * Initialize the object.
     * 
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(HttpRequest $request, Api $api)
    {
        // store the symfony request object
        $this->request = $request;
        $this->rawPost = str_replace(array('[undefined]'),
                                     array('null'),
                                     $request->getContent());
        $this->post = $request->request->all();
        
        foreach($request->files->keys() as $key)
            $this->files[$key] = $request->files->get($key, array());

        $this->api = $api;
    }

    /**
     * Return the files from call.
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * Get the direct calls object.
     *
     * @return array
     */
    public function getCalls()
    {
        if (null == $this->calls) {
            $this->calls = $this->extractCalls();
        }

        return $this->calls;
    }

    /**
     * Extract the ExtDirect calls from request.
     *
     * @return array
     */
    public function extractCalls()
    {
        $calls = array();

        if ((bool)$this->request->request->keys()) {
            $calls[] = new CallForm($this->post, $this);
        } elseif ($this->rawPost) {
            $decoded = json_decode($this->rawPost);
            $decoded = !is_array($decoded) ? array($decoded) : $decoded;
            
            array_walk_recursive($decoded, array($this, 'parseRawToArray'));
            // @todo: check utf8 config option from bundle
            //array_walk_recursive($decoded, array($this, 'decode'));

            if(is_null($decoded) || !is_array($decoded))
                throw new InvalidJsonException(sprintf('I can\'t parse input json: "%s"', $this->rawPost));
            
            foreach ($decoded as $call) {
                $call = new Call((array)$call, $this);
                $call->setApi($this->api);
                $calls[] = $call;
            }
        }
        
        return $calls;
    }

    /**
     * Parse a raw http post to a php array.
     * 
     * @param mixed  $value
     * @param string $key
     */
    private function parseRawToArray(&$value, &$key)
    {
        // parse a json string to an array
        if (is_string($value)) {

            $pos = substr($value,0,1);
            if ($pos == '[' || $pos == '(' || $pos == '{') {
                $json = json_decode($value);
            } else {
                $json = $value;
            }
            
            if ($json) {                
                $value = $json;
            }
        }

        // if the value is an object, parse it to an array
        if (is_object($value)) {
            $value = (array)$value;
        }

        // call the recursive function to all keys of array
        if (is_array($value)) {
            array_walk_recursive($value, array($this, 'parseRawToArray'));
        }
    }
}
