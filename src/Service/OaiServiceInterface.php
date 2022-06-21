<?php

namespace Subugoe\OaiBundle\Service;

use Subugoe\OaiBundle\Exception\OaiException;
use Subugoe\OaiModel\Model\Identify\Identify;
use Subugoe\OaiModel\Model\MetadataFormats;
use Subugoe\OaiModel\Model\Sets;

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
