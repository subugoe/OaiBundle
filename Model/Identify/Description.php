<?php

namespace Subugoe\OaiBundle\Model\Identify;

class Description
{
    /**
     * @var OaiIdentifier
     */
    private $oaiIdentifier;

    /**
     * @return OaiIdentifier
     */
    public function getOaiIdentifier(): OaiIdentifier
    {
        return $this->oaiIdentifier;
    }

    /**
     * @param OaiIdentifier $oaiIdentifier
     *
     * @return Description
     */
    public function setOaiIdentifier(OaiIdentifier $oaiIdentifier): Description
    {
        $this->oaiIdentifier = $oaiIdentifier;

        return $this;
    }
}
