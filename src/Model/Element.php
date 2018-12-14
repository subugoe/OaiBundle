<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Subugoe\OaiBundle\Model\Element\OaiPmh;
use Subugoe\OaiBundle\Model\Element\ResumptionToken;

/**
 * @Serializer\XmlRoot("OAI-PMH")
 * @Serializer\XmlNamespace(uri="http://www.openarchives.org/OAI/2.0/")
 * @Serializer\XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @Serializer\XmlNamespace(uri="http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd", prefix="schemaLocation")
 */
class Element
{
    /**
     * @var OaiPmh
     */
    private $oaiPmh;

    /**
     * @var \Subugoe\OaiBundle\Model\Element\Request
     */
    private $request;

    /**
     * @var ResumptionToken
     */
    private $resumptionToken;

    /**
     * @var ArrayCollection
     */
    private $listRecordElements;

    public function __construct()
    {
        $this->listRecordElements = new ArrayCollection();
    }

    /**
     * @return OaiPmh
     */
    public function getOaiPmh(): OaiPmh
    {
        return $this->oaiPmh;
    }

    /**
     * @param OaiPmh $oaiPmh
     *
     * @return Element
     */
    public function setOaiPmh(OaiPmh $oaiPmh): Element
    {
        $this->oaiPmh = $oaiPmh;

        return $this;
    }

    /**
     * @return ResumptionToken
     */
    public function getResumptionToken(): ResumptionToken
    {
        return $this->resumptionToken;
    }

    /**
     * @param ResumptionToken $resumptionToken
     *
     * @return Element
     */
    public function setResumptionToken(ResumptionToken $resumptionToken): Element
    {
        $this->resumptionToken = $resumptionToken;

        return $this;
    }

    /**
     * @return Element\Request
     */
    public function getRequest(): Element\Request
    {
        return $this->request;
    }

    /**
     * @param Element\Request $request
     *
     * @return Element
     */
    public function setRequest(Element\Request $request): Element
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getListRecordElements(): ArrayCollection
    {
        return $this->listRecordElements;
    }

    /**
     * @param ArrayCollection $listRecordElements
     *
     * @return Element
     */
    public function setListRecordElements(ArrayCollection $listRecordElements): Element
    {
        $this->listRecordElements = $listRecordElements;

        return $this;
    }
}
