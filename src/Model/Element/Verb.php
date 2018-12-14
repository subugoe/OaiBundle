<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use Doctrine\Common\Collections\ArrayCollection;
use MyCLabs\Enum\Enum;

class Verb extends Enum
{
    const LIST_RECORDS = 'ListRecords';

    /**
     * @var ArrayCollection
     */
    private $records;

    /**
     * @var ResumptionToken
     */
    private $resumptionToken;

    /**
     * @var Header
     */
    private $header;

    public function __construct($value)
    {
        parent::__construct($value);
        $this->records = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getRecords(): ArrayCollection
    {
        return $this->records;
    }

    /**
     * @param ArrayCollection $records
     *
     * @return Verb
     */
    public function setRecords(ArrayCollection $records): Verb
    {
        $this->records = $records;

        return $this;
    }

    public function addRecord(Record $record)
    {
        $this->records->add($record);
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
     * @return Verb
     */
    public function setResumptionToken(ResumptionToken $resumptionToken): Verb
    {
        $this->resumptionToken = $resumptionToken;

        return $this;
    }

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * @param Header $header
     *
     * @return Verb
     */
    public function setHeader(Header $header): Verb
    {
        $this->header = $header;

        return $this;
    }
}
