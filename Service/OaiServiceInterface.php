<?php

namespace Subugoe\OaiBundle\Service;

use Subugoe\OaiBundle\Exception\OaiException;

interface OaiServiceInterface
{
    /**
     * @throws OaiException
     */
    public function start(): string;

    public function deleteExpiredResumptionTokens();
}
