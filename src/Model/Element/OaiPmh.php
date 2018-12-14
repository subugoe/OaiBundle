<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use Doctrine\Common\Collections\ArrayCollection;

class OaiPmh
{
    /**
     * @var ArrayCollection
     */
    private $attributes;

    /**
     * @var \DateTime
     */
    private $responseDate;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Verb
     */
    private $verb;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getResponseDate(): \DateTime
    {
        return $this->responseDate;
    }

    /**
     * @param \DateTime $responseDate
     *
     * @return OaiPmh
     */
    public function setResponseDate(\DateTime $responseDate): OaiPmh
    {
        $this->responseDate = $responseDate;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection $attributes
     *
     * @return OaiPmh
     */
    public function setAttributes(ArrayCollection $attributes): OaiPmh
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addAttribute($key, $value)
    {
        $this->attributes->set($key, $value);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return OaiPmh
     */
    public function setRequest(Request $request): OaiPmh
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Verb
     */
    public function getVerb(): Verb
    {
        return $this->verb;
    }

    /**
     * @param Verb $verb
     *
     * @return OaiPmh
     */
    public function setVerb(Verb $verb): OaiPmh
    {
        $this->verb = $verb;

        return $this;
    }
}
