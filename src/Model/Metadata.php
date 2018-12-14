<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model;


use Doctrine\Common\Collections\ArrayCollection;

class Metadata
{
    /**
     * @var ArrayCollection
     */
    private $elements;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getElements(): ArrayCollection
    {
        return $this->elements;
    }

    /**
     * @param ArrayCollection $elements
     * @return Metadata
     */
    public function setElements(ArrayCollection $elements): Metadata
    {
        $this->elements = $elements;
        return $this;
    }

    public function addElement(string $element)
    {
        $this->elements->add($element);
    }
}
