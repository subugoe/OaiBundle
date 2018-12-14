<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use Doctrine\Common\Collections\ArrayCollection;

class Request
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var ArrayCollection
     */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Request
     */
    public function setUrl(string $url): Request
    {
        $this->url = $url;

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
     * @return Request
     */
    public function setAttributes(ArrayCollection $attributes): Request
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addAttribute($key, $value)
    {
        $this->attributes->set($key, $value);
    }
}
