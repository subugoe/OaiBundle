<?php

namespace Subugoe\OaiBundle\Model\Identify;

class Description
{
    private ?\Subugoe\OaiBundle\Model\Identify\OaiIdentifier $oaiIdentifier = null;

    public function getOaiIdentifier(): OaiIdentifier
    {
        return $this->oaiIdentifier;
    }

    public function setOaiIdentifier(OaiIdentifier $oaiIdentifier): self
    {
        $this->oaiIdentifier = $oaiIdentifier;

        return $this;
    }
}
