<?php

namespace Ext\DirectBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Represents a @Remote annotation.
 *
 * @Annotation
 * @Target("METHOD")
 * 
 */
class Remote extends ConfigurationAnnotation
{
    /**
     *  Comment here.
     *
     * @var string
     */
    protected $root = NULL;

    /**
     *  Comment here.
     *
     * @var string
     */
    protected $successProperty = "success";

    /**
     *  Comment here.
     *
     * @var string
     */
    protected $totalProperty = "total";

    /**
     *  Comment here.
     *
     * @param string $root Comment here.
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     *  Comment here.
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     *  Comment here.
     *
     * @param string $successProperty Comment here.
     */
    public function setSuccessProperty($successProperty)
    {
        $this->successProperty = $successProperty;
    }

    /**
     *  Comment here.
     *
     * @return string
     */
    public function getSuccessProperty()
    {
        return $this->successProperty;
    }

    /**
     *  Comment here.
     *
     * @param string $totalProperty Comment here.
     */
    public function setTotalProperty($totalProperty)
    {
        $this->totalProperty = $totalProperty;
    }

    /**
     *  Comment here.
     *
     * @return string
     */
    public function getTotalProperty()
    {
        return $this->totalProperty;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'remote';
    }

    /**
     * Only one cache directive is allowed
     *
     * @return Boolean
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return false;
    }
}