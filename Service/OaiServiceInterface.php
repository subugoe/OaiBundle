<?php

namespace Subugoe\OaiBundle\Service;

use Subugoe\OaiBundle\Exception\OaiException;
use Subugoe\OaiBundle\Model\Identify\Identify;
use Subugoe\OaiBundle\Model\MetadataFormats;
use Subugoe\OaiBundle\Model\Sets;

interface OaiServiceInterface
{
    /**
     * @throws OaiException
     */
    public function start(): string;

    public function deleteExpiredResumptionTokens(): void;

    public function getIdentify(string $url, array $oaiConfiguration): Identify;

    public function getMetadataFormats(string $url, array $oaiConfiguration, ?string $identifier): MetadataFormats;

    public function getListSets(string $url, array $oaiConfiguration): Sets;
}
