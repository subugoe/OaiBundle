<?php

namespace Subugoe\OaiBundle\Model\Identify;

use JMS\Serializer\Annotation as Serializer;

class OaiIdentifier
{
    /**
     * @Serializer\XmlElement(cdata=false)
     */
    private ?string $delimiter = null;

    /**
     * @Serializer\SerializedName("xmlns")
     * @Serializer\XmlAttribute
     */
    private ?string $namespace = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("repositoryIdentifier")
     */
    private ?string $repositoryIdentifier = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("sampleIdentifier")
     */
    private ?string $sampleIdentifier = null;

    /**
     * @Serializer\SerializedName("xsi:schemaLocation")
     * @Serializer\XmlAttribute
     */
    private ?string $schemaLocation = null;
    /**
     * @Serializer\XmlElement(cdata=false)
     */
    private ?string $scheme = null;

    /**
     * @Serializer\SerializedName("xmlns:xsi")
     * @Serializer\XmlAttribute
     */
    private ?string $xsi = null;

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getRepositoryIdentifier(): string
    {
        return $this->repositoryIdentifier;
    }

    public function getSampleIdentifier(): string
    {
        return $this->sampleIdentifier;
    }

    public function getSchemaLocation(): string
    {
        return $this->schemaLocation;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getXsi(): string
    {
        return $this->xsi;
    }

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setRepositoryIdentifier(string $repositoryIdentifier): self
    {
        $this->repositoryIdentifier = $repositoryIdentifier;

        return $this;
    }

    public function setSampleIdentifier(string $sampleIdentifier): self
    {
        $this->sampleIdentifier = $sampleIdentifier;

        return $this;
    }

    public function setSchemaLocation(string $schemaLocation): self
    {
        $this->schemaLocation = $schemaLocation;

        return $this;
    }

    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function setXsi(string $xsi): self
    {
        $this->xsi = $xsi;

        return $this;
    }
}
