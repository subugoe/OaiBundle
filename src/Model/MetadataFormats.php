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

    public function getMetadataFormats(): array
    {
        return $this->metadataFormats;
    }

    public function setMetadataFormats(array $metadataFormats): self
    {
        $this->metadataFormats = $metadataFormats;

        return $this;
    }
}
