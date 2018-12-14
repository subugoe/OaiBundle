<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Model\Element;

class ResumptionToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * 'Y-m-d\TH:i:s\Z'.
     *
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var string
     */
    private $completeListSize;

    /**
     * @var string
     */
    private $cursor;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return ResumptionToken
     */
    public function setToken(string $token): ResumptionToken
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate(): \DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTime $expirationDate
     *
     * @return ResumptionToken
     */
    public function setExpirationDate(\DateTime $expirationDate): ResumptionToken
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompleteListSize(): string
    {
        return $this->completeListSize;
    }

    /**
     * @param string $completeListSize
     *
     * @return ResumptionToken
     */
    public function setCompleteListSize(string $completeListSize): ResumptionToken
    {
        $this->completeListSize = $completeListSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * @param string $cursor
     *
     * @return ResumptionToken
     */
    public function setCursor(string $cursor): ResumptionToken
    {
        $this->cursor = $cursor;

        return $this;
    }
}
