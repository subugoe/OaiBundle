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

    /**
     * @return array
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    /**
     * @param array $sets
     *
     * @return Sets
     */
    public function setSets(array $sets): Sets
    {
        $this->sets = $sets;

        return $this;
    }
}
