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
     * @Serializer\XmlValue
     * @Serializer\XmlElement(cdata=false)
     */
    private $url;

    /**
     * @var string
     * @Serializer\XmlAttribute
     */
    private $verb;

    /**
     * @var string
     * @Serializer\XmlAttribute
     */
    private $identifier;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Request
     */
    public function setUrl(string $url): Request
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * @param string $verb
     *
     * @return Request
     */
    public function setVerb(string $verb): Request
    {
        $this->verb = $verb;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return Request
     */
    public function setIdentifier(string $identifier): Request
    {
        $this->identifier = $identifier;

        return $this;
    }
}
