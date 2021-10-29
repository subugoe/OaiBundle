<?php

namespace Subugoe\OaiBundle\Model;

use Subugoe\IIIFModel\Model\Document;

class Results
{
    /**
     * @var array
     */
    private $documents = [];
    /**
     * @var int
     */
    private $foundCount = 0;

    public function addDocument(Document $document)
    {
        $this->documents[] = $document;
    }

    public function getDocument(int $position): Document
    {
        return $this->documents[$position];
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function getFoundCount(): int
    {
        return $this->foundCount;
    }

    /**
     * @return Results
     */
    public function setDocuments(array $documents): self
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return Results
     */
    public function setFoundCount(int $foundCount): self
    {
        $this->foundCount = $foundCount;

        return $this;
    }
}
