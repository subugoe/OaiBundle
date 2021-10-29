<?php

namespace Subugoe\OaiBundle\Model\Identify;

use Subugoe\OaiBundle\Model\Oai;

class Identify extends Oai
{
    private ?\Subugoe\OaiBundle\Model\Identify\Identification $identify = null;

    public function getIdentify(): Identification
    {
        return $this->identify;
    }

    /**
     * @return Identify
     */
    public function setIdentify(Identification $identify): self
    {
        $this->identify = $identify;

        return $this;
    }
}
