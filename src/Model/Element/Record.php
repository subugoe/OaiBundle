<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

use Subugoe\OaiBundle\Model\Metadata;

class Record
{
    /**
     * @var Header
     */
    private $header;

    /**
     * @var Metadata
     */
    private $metadata;

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
     * @return Record
     */
    public function setHeader(Header $header): Record
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     * @return Record
     */
    public function setMetadata(Metadata $metadata): Record
    {
        $this->metadata = $metadata;
        return $this;
    }


}
