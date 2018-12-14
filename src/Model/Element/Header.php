<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use Doctrine\Common\Collections\ArrayCollection;

class Header
{
    /**
     * @var ArrayCollection
     */
    private $headerElements;

    public function __construct()
    {
        $this->headerElements = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getHeaderElements(): ArrayCollection
    {
        return $this->headerElements;
    }

    /**
     * @param ArrayCollection $headerElements
     *
     * @return Header
     */
    public function setHeaderElements(ArrayCollection $headerElements): Header
    {
        $this->headerElements = $headerElements;

        return $this;
    }

    public function addHeaderElement(HeaderElement $headerElement)
    {
        $this->headerElements->add($headerElement);
    }
}
