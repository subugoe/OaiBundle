<?php

namespace Subugoe\OaiBundle\Model\Identify;

use JMS\Serializer\Annotation as Serializer;

class Identification
{
    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("adminEmail")
     */
    private ?string $adminEmail = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("baseURL")
     */
    private ?string $baseUrl = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("deletedRecord")
     */
    private ?string $deletedRecord = null;

    private ?\Subugoe\OaiBundle\Model\Identify\Description $description = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("earliestDatestamp")
     */
    private ?\DateTimeImmutable $earliestDatestamp = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     */
    private ?string $granularity = null;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("protocolVersion")
     */
    private ?string $protocolVersion = null;
    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("repositoryName")
     */
    private ?string $repositoryName = null;

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

    public function setAdminEmail(string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function setDeletedRecord(string $deletedRecord): self
    {
        $this->deletedRecord = $deletedRecord;

        return $this;
    }

    public function setDescription(Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setEarliestDatestamp(\DateTimeImmutable $earliestDatestamp): self
    {
        $this->earliestDatestamp = $earliestDatestamp;

        return $this;
    }

    public function setGranularity(string $granularity): self
    {
        $this->granularity = $granularity;

        return $this;
    }

    public function setProtocolVersion(string $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    public function setRepositoryName(string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }
}
