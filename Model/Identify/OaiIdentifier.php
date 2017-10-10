<?php

namespace Subugoe\OaiBundle\Model\Identify;

use JMS\Serializer\Annotation as Serializer;

class OaiIdentifier
{
    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     */
    private $scheme;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("repositoryIdentifier")
     */
    private $repositoryIdentifier;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     */
    private $delimiter;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("sampleIdentifier")
     */
    private $sampleIdentifier;

    /**
     * @var string
     * @Serializer\SerializedName("xmlns")
     * @Serializer\XmlAttribute
     */
    private $namespace;

    /**
     * @var string
     * @Serializer\SerializedName("xmlns:xsi")
     * @Serializer\XmlAttribute
     */
    private $xsi;

    /**
     * @var string
     * @Serializer\SerializedName("xsi:schemaLocation")
     * @Serializer\XmlAttribute
     */
    private $schemaLocation;

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     *
     * @return OaiIdentifier
     */
    public function setScheme(string $scheme): OaiIdentifier
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepositoryIdentifier(): string
    {
        return $this->repositoryIdentifier;
    }

    /**
     * @param string $repositoryIdentifier
     *
     * @return OaiIdentifier
     */
    public function setRepositoryIdentifier(string $repositoryIdentifier): OaiIdentifier
    {
        $this->repositoryIdentifier = $repositoryIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     *
     * @return OaiIdentifier
     */
    public function setDelimiter(string $delimiter): OaiIdentifier
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function getSampleIdentifier(): string
    {
        return $this->sampleIdentifier;
    }

    /**
     * @param string $sampleIdentifier
     *
     * @return OaiIdentifier
     */
    public function setSampleIdentifier(string $sampleIdentifier): OaiIdentifier
    {
        $this->sampleIdentifier = $sampleIdentifier;

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
     * @return OaiIdentifier
     */
    public function setNamespace(string $namespace): OaiIdentifier
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getXsi(): string
    {
        return $this->xsi;
    }

    /**
     * @param string $xsi
     *
     * @return OaiIdentifier
     */
    public function setXsi(string $xsi): OaiIdentifier
    {
        $this->xsi = $xsi;

        return $this;
    }

    /**
     * @return string
     */
    public function getSchemaLocation(): string
    {
        return $this->schemaLocation;
    }

    /**
     * @param string $schemaLocation
     *
     * @return OaiIdentifier
     */
    public function setSchemaLocation(string $schemaLocation): OaiIdentifier
    {
        $this->schemaLocation = $schemaLocation;

        return $this;
    }
}
