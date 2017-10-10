<?php

namespace Subugoe\OaiBundle\Model\Identify;

use JMS\Serializer\Annotation as Serializer;

class Identification
{
    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("repositoryName")
     */
    private $repositoryName;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("baseURL")
     */
    private $baseUrl;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("protocolVersion")
     */
    private $protocolVersion;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("adminEmail")
     */
    private $adminEmail;

    /**
     * @var \DateTimeImmutable
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("earliestDatestamp")
     */
    private $earliestDatestamp;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("deletedRecord")
     */
    private $deletedRecord;

    /**
     * @var string
     * @Serializer\XmlElement(cdata=false)
     */
    private $granularity;

    /**
     * @var Description
     */
    private $description;

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @param string $repositoryName
     *
     * @return Identification
     */
    public function setRepositoryName(string $repositoryName): Identification
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return Identification
     */
    public function setBaseUrl(string $baseUrl): Identification
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $protocolVersion
     *
     * @return Identification
     */
    public function setProtocolVersion(string $protocolVersion): Identification
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    /**
     * @param string $adminEmail
     *
     * @return Identification
     */
    public function setAdminEmail(string $adminEmail): Identification
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEarliestDatestamp(): \DateTimeImmutable
    {
        return $this->earliestDatestamp;
    }

    /**
     * @param \DateTimeImmutable $earliestDatestamp
     *
     * @return Identification
     */
    public function setEarliestDatestamp(\DateTimeImmutable $earliestDatestamp): Identification
    {
        $this->earliestDatestamp = $earliestDatestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeletedRecord(): string
    {
        return $this->deletedRecord;
    }

    /**
     * @param string $deletedRecord
     *
     * @return Identification
     */
    public function setDeletedRecord(string $deletedRecord): Identification
    {
        $this->deletedRecord = $deletedRecord;

        return $this;
    }

    /**
     * @return string
     */
    public function getGranularity(): string
    {
        return $this->granularity;
    }

    /**
     * @param string $granularity
     *
     * @return Identification
     */
    public function setGranularity(string $granularity): Identification
    {
        $this->granularity = $granularity;

        return $this;
    }

    /**
     * @return Description
     */
    public function getDescription(): Description
    {
        return $this->description;
    }

    /**
     * @param Description $description
     *
     * @return Identification
     */
    public function setDescription(Description $description): Identification
    {
        $this->description = $description;

        return $this;
    }
}
