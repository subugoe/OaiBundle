<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use MyCLabs\Enum\Enum;

class HeaderElement extends Enum
{
    const IDENTIFIER = 'identifier';
    const DATESTAMP = 'datestamp';
    const SET_SPEC = 'setSpec';

    /**
     * @var string
     */
    private $elementValue;

    /**
     * @return string
     */
    public function getElementValue(): string
    {
        return $this->elementValue;
    }

    /**
     * @param string $elementValue
     *
     * @return HeaderElement
     */
    public function setElementValue(string $elementValue): HeaderElement
    {
        $this->elementValue = $elementValue;

        return $this;
    }
}
