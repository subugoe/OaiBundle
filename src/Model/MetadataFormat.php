<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class MetadataFormat
{
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

    /**
     * @var string
     * @Serializer\SerializedName("metadataNamespace")
     * @Serializer\XmlElement(cdata=false)
     */
    private $namespace;

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return MetadataFormat
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     *
     * @return MetadataFormat
     */
    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     *
     * @return MetadataFormat
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }
}
