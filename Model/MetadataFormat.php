<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class MetadataFormat
{
    /**
     * @var string
     * @Serializer\SerializedName("metadataNamespace")
     * @Serializer\XmlElement(cdata=false)
     */
    private $namespace;
    /**
     * @var string
     * @Serializer\SerializedName("metadataPrefix")
     * @Serializer\XmlElement(cdata=false)
     */
    private $prefix;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     */
    private $schema;

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return MetadataFormat
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return MetadataFormat
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return MetadataFormat
     */
    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }
}
