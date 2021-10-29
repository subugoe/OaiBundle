<?php

namespace Subugoe\OaiBundle\Model\Identify;

use JMS\Serializer\Annotation as Serializer;

class Identification
{
    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("adminEmail")
     */
    private $adminEmail;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("baseURL")
     */
    private $baseUrl;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("deletedRecord")
     */
    private $deletedRecord;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var \DateTimeImmutable
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("earliestDatestamp")
     */
    private $earliestDatestamp;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     */
    private $granularity;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("protocolVersion")
     */
    private $protocolVersion;
    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("repositoryName")
     */
    private $repositoryName;

    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getDeletedRecord(): string
    {
        return $this->deletedRecord;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getEarliestDatestamp(): \DateTimeImmutable
    {
        return $this->earliestDatestamp;
    }

    public function getGranularity(): string
    {
        return $this->granularity;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @return Identification
     */
    public function setAdminEmail(string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setDeletedRecord(string $deletedRecord): self
    {
        $this->deletedRecord = $deletedRecord;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setDescription(Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setEarliestDatestamp(\DateTimeImmutable $earliestDatestamp): self
    {
        $this->earliestDatestamp = $earliestDatestamp;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setGranularity(string $granularity): self
    {
        $this->granularity = $granularity;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setProtocolVersion(string $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    /**
     * @return Identification
     */
    public function setRepositoryName(string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }
}
