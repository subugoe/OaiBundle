<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * Request data for OAI Harvesting.
 */
class Request
{
    /**
     * @var string
     * @Serializer\XmlAttribute
     */
    private $identifier;
    /**
     * @var string
     * @Serializer\XmlValue
     * @Serializer\XmlElement(cdata=false)
     */
    private $url;

    /**
     * @var string
     * @Serializer\XmlAttribute
     */
    private $verb;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * @return Request
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return Request
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Request
     */
    public function setVerb(string $verb): self
    {
        $this->verb = $verb;

        return $this;
    }
}
