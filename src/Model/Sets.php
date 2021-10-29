<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class Sets extends Oai
{
    /**
     * @var array
     * @Serializer\SerializedName("ListSets")
     * @Serializer\XmlList(inline = true, entry = "set")
     */
    private $sets;

    public function getSets(): array
    {
        return $this->sets;
    }

    /**
     * @return Sets
     */
    public function setSets(array $sets): self
    {
        $this->sets = $sets;

        return $this;
    }
}
