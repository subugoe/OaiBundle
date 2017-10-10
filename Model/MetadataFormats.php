<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class MetadataFormats extends Oai
{
    /**
     * @var array
     * @Serializer\SerializedName("ListMetadataFormats")
     * @Serializer\XmlList(entry = "metadataFormat")
     */
    private $metadataFormats;

    /**
     * @return array
     */
    public function getMetadataFormats(): array
    {
        return $this->metadataFormats;
    }

    /**
     * @param array $metadataFormats
     *
     * @return MetadataFormats
     */
    public function setMetadataFormats(array $metadataFormats): MetadataFormats
    {
        $this->metadataFormats = $metadataFormats;

        return $this;
    }
}
