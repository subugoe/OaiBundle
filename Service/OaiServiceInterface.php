<?php

namespace Subugoe\OaiBundle\Service;

use Subugoe\OaiBundle\Exception\OaiException;
use Subugoe\OaiBundle\Model\Identify\Identify;
use Subugoe\OaiBundle\Model\MetadataFormats;
use Subugoe\OaiBundle\Model\Sets;

interface OaiServiceInterface
{
    public function deleteExpiredResumptionTokens(): void;

    public function getIdentify(string $url, array $oaiConfiguration): Identify;

    public function getListSets(string $url, array $oaiConfiguration): Sets;

    public function getMetadataFormats(string $url, array $oaiConfiguration, ?string $identifier): MetadataFormats;

    /**
     * @throws OaiException
     */
    public function start(): string;
}
