<?php

namespace Subugoe\OaiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("OAI-PMH")
 * @Serializer\XmlNamespace(uri="http://www.openarchives.org/OAI/2.0/")
 * @Serializer\XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @Serializer\XmlNamespace(uri="http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd", prefix="schemaLocation")
 */
class Oai
{
    /**
     * @var \DateTimeImmutable
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\SerializedName("responseDate")
     */
    private $date;

    /**
     * @var Request
     * @Serializer\XmlElement(cdata=false)
     */
    private $request;

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     *
     * @return Oai
     */
    public function setDate(\DateTimeImmutable $date): Oai
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return Oai
     */
    public function setRequest(Request $request): Oai
    {
        $this->request = $request;

        return $this;
    }
}
