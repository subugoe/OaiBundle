<?php

namespace Subugoe\OaiBundle\Model\Identify;

use Subugoe\OaiBundle\Model\Oai;

class Identify extends Oai
{
    /**
     * @var Identification
     */
    private $identify;

    /**
     * @return Identification
     */
    public function getIdentify(): Identification
    {
        return $this->identify;
    }

    /**
     * @param Identification $identify
     *
     * @return Identify
     */
    public function setIdentify(Identification $identify): self
    {
        $this->identify = $identify;

        return $this;
    }
}
