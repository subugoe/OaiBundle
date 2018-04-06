<?php

namespace Subugoe\OaiBundle\Model;

use Subugoe\IIIFBundle\Model\Document;

class Results
{
    /**
     * @var int
     */
    private $foundCount = 0;

    /**
     * @var array
     */
    private $documents = [];

    /**
     * @return int
     */
    public function getFoundCount(): int
    {
        return $this->foundCount;
    }

    /**
     * @param int $foundCount
     *
     * @return Results
     */
    public function setFoundCount(int $foundCount): self
    {
        $this->foundCount = $foundCount;

        return $this;
    }

    /**
     * @return array
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @param array $documents
     *
     * @return Results
     */
    public function setDocuments(array $documents): self
    {
        $this->documents = $documents;

        return $this;
    }

    public function addDocument(Document $document)
    {
        $this->documents[] = $document;
    }

    public function getDocument(int $position): Document
    {
        return $this->documents[$position];
    }
}
