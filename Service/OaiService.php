<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Service;

use League\Flysystem\Filesystem;
use Subugoe\OaiBundle\Exception\OaiException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Solarium\Client;
use Subugoe\IIIFModel\Model\Document;
use Subugoe\IIIFBundle\Translator\TranslatorInterface;
use Subugoe\OaiBundle\Model\Collection;
use Subugoe\OaiBundle\Model\Identify\Description;
use Subugoe\OaiBundle\Model\Identify\Identification;
use Subugoe\OaiBundle\Model\Identify\Identify;
use Subugoe\OaiBundle\Model\Identify\OaiIdentifier;
use Subugoe\OaiBundle\Model\MetadataFormat;
use Subugoe\OaiBundle\Model\MetadataFormats;
use Subugoe\OaiBundle\Model\Results;
use Subugoe\OaiBundle\Model\Sets;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OaiService implements OaiServiceInterface
{
    /**
     * @var \DOMDocument
     */
    public $oai;

    /**
     * @var \DOMElement
     */
    private $oai_pmh;

    /**
     * @var \DOMElement
     */
    private $request;

    /**
     * @var \DOMElement
     */
    private $record;

    /**
     * @var \DOMElement
     */
    private $head;

    /**
     * @var \DOMElement
     */
    private $oai_dc;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FilesystemInterface
     */
    private $oaiTempDirectory;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $oaiConfiguration;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translation;

    /**
     * OaiService constructor.
     * @param TranslatorInterface $translator
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translation
     */
    public function __construct(
        TranslatorInterface $translator,
        \Symfony\Contracts\Translation\TranslatorInterface $translation
    ) {
        $this->translator = $translator;
        $this->translation = $translation;
    }

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->oaiTempDirectory = $filesystem;
    }

    public function setOaiConfiguration(array $config): void
    {
        $this->oaiConfiguration = $config;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getIdentify(string $url, array $oaiConfiguraion): Identify
    {
        $identify = new Identify();
        $identification = new Identification();
        $description = new Description();
        $oaiIdentifier = new OaiIdentifier();
        $oaiIdentifierTags = $oaiConfiguraion['oai_identifier'];

        $oaiIdentifier
            ->setNamespace($oaiIdentifierTags['xmlns'])
            ->setXsi($oaiIdentifierTags['xmlns_xsi'])
            ->setSchemaLocation($oaiIdentifierTags['xsi_schema_location'])
            ->setScheme($oaiIdentifierTags['scheme'])
            ->setDelimiter($oaiIdentifierTags['delimiter'])
            ->setRepositoryIdentifier($oaiIdentifierTags['repository_identifier'])
            ->setSampleIdentifier($oaiIdentifierTags['sample_identifier']);

        $description->setOaiIdentifier($oaiIdentifier);
        $identificationTags = $oaiConfiguraion['identification_tags'];
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($url)
            ->setVerb('Identify');
        $identify
            ->setDate(new \DateTimeImmutable())
            ->setRequest($oaiRequest);
        $identification
            ->setAdminEmail($identificationTags['admin_email'])
            ->setBaseUrl($identificationTags['base_url'])
            ->setDeletedRecord($identificationTags['deleted_record'])
            ->setGranularity($identificationTags['granularity'])
            ->setProtocolVersion($identificationTags['protocol_version'])
            ->setRepositoryName($identificationTags['repository_name'])
            ->setEarliestDatestamp(new \DateTimeImmutable('1998-03-01T00:00:00Z'))
            ->setDescription($description);

        $identify->setIdentify($identification);

        return $identify;
    }

    public function getMetadataFormats(string $url, array $oaiConfiguraion, ?string $identifer): MetadataFormats
    {
        $metadataFormats = new MetadataFormats();
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($url)
            ->setVerb('ListMetadataFormats');

        if ($identifer) {
            $oaiRequest->setIdentifier($identifer);
        }
        $metadataFormats->setDate(new \DateTimeImmutable())
            ->setRequest($oaiRequest);

        $formats = [];
        $availableFormats = $oaiConfiguraion['metadata_formats'];
        foreach ($availableFormats as $availableFormat) {
            $metadataFormat = new MetadataFormat();
            $namespace = $availableFormat['namespace'];
            $schema = $availableFormat['schema'];
            if (is_array($namespace)) {
                $namespace = implode(' ', $availableFormat['namespace']);
                $schema = implode(' ', $schema);
            }

            $metadataFormat
                ->setPrefix($availableFormat['prefix'])
                ->setSchema($schema)
                ->setNamespace($namespace);
            $formats[] = $metadataFormat;
        }

        $metadataFormats->setMetadataFormats($formats);

        return $metadataFormats;
    }

    public function getListSets(string $url, array $collections): Sets
    {
        $sets = new Sets();
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($url)
            ->setVerb('ListSets');

        $sets->setDate(new \DateTimeImmutable())
            ->setRequest($oaiRequest);

        $collectionStorage = [];
        foreach ($collections as $collection) {
            $collectionItem = new Collection();
            $collectionItem
                ->setId(sprintf('dc_%s', $collection['id']))
                ->setLabel($this->translation->trans($collection['id']));
            $collectionStorage[] = $collectionItem;
        }
        $sets->setSets($collectionStorage);

        return $sets;
    }

    public function start(): string
    {
        // create XML-DOM
        $this->oai = new \DOMDocument('1.0', 'UTF-8');

        //nice output format (linebreaks and tabs)
        $this->oai->formatOutput = false;

        //insert xsl
        $this->oai->appendChild($this->oai->createProcessingInstruction('xml-stylesheet', 'href="/bundles/subugoeoai/xsl/oai2.xsl" type="text/xsl"'));

        $this->createRootElement();

        $requestArguments = $this->parseArguments($this->requestStack->getMasterRequest()->query->all());

        //if isset requestArguments['start'] no more checks!
        if (!isset($requestArguments['start']) && isset($requestArguments['verb'])) {
            if (isset($this->oaiConfiguration['metadata_format_options'][$requestArguments['verb']]['requiredArguments'])) {
                $this->errorRequiredArguments($requestArguments['verb'], $requestArguments);
            }
            $this->errorAllowedArguments($requestArguments['verb'], $requestArguments);
        }

        if (!isset($requestArguments['from']) && isset($requestArguments['until'])) {
            if (isset($this->oaiConfiguration['metadata_format_options'][$requestArguments['verb']]['requiredArguments'])) {
                $this->errorRequiredArguments($requestArguments['verb'], $requestArguments);
            }
            $this->errorAllowedArguments($requestArguments['verb'], $requestArguments);
        }

        if ('GetRecord' === $requestArguments['verb']) {
            $this->getRecord($requestArguments);
        } elseif ('ListRecords' === $requestArguments['verb'] || 'ListIdentifiers' === $requestArguments['verb']) {
            $this->getListRecordsAndListIdentifiers($requestArguments);
        }

        return $this->oai->saveXML();
    }

    public function deleteExpiredResumptionTokens(): void
    {
        $time = time() - 259200;
        $contents = $this->oaiTempDirectory->listContents('/oai-gdz/');
        foreach ($contents as $object) {
            if ($object['timestamp'] < $time) {
                $this->oaiTempDirectory->delete($object['path']);
            }
        }
    }

    protected function listRecords(array $requestArguments, array $result, $key): void
    {
        $metadata = $this->oai->createElement('metadata');
        $this->record->appendChild($metadata);
        switch ($requestArguments['metadataPrefix']) {
            case 'oai_dc':
                $this->listOaiDcRecords($result, $key, $metadata);
                break;
            case 'mets':
                $this->listMetsRecords($result, $key, $metadata);
                break;
        }
    }

    /**
     * @param array $requestArguments
     *
     * @throws OaiException
     */
    private function errorDate(array &$requestArguments)
    {
        $arrDates = ['from' => '00:00:00', 'until' => '23:59:59'];
        foreach ($arrDates as $key => $val) {
            if (isset($requestArguments[$key])) {
                preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})(([T]{1})([0-9]{2}):([0-9]{2}):([0-9]{2})([Z]{1}){1})?/', $requestArguments[$key], $regs);
                if ('' !== $regs[1] && isset($regs[4]) && '' !== $regs[4]) {
                    $requestArguments['DB'.$key] = $regs[1].'-'.$regs[2].'-'.$regs[3].' '.$regs[6].':'.$regs[7].':'.$regs[8];
                } else {
                    if ('' !== $regs[1] && 10 === strlen($requestArguments[$key])) {
                        $requestArguments['DB'.$key] = $regs[1].'-'.$regs[2].'-'.$regs[3].' '.$val;
                    } else {
                        throw new OaiException(sprintf('Bad argument. %s: %s', $key, $requestArguments[$key]), 1478853737);
                    }
                }
            }
        }
    }

    /**
     * @param array $requestArguments
     *
     * @throws OaiException
     */
    private function errorFromUntil(array &$requestArguments): void
    {
        if (isset($requestArguments['from'], $requestArguments['until'])) {
            if ((strlen($requestArguments['from'])) !== (strlen($requestArguments['until']))) {
                throw new OaiException(sprintf('Bad argument. from: %s until %s', $requestArguments['from'], $requestArguments['until']), 1478852818);
            }

            if (($requestArguments['from']) > ($requestArguments['until'])) {
                throw new OaiException(sprintf('Bad argument. from: %s until %s', $requestArguments['from'], $requestArguments['until']), 1478852845);
            }
        }
    }

    /**
     * @param array $requestArguments
     *
     * @throws OaiException
     */
    private function errorMetaDataPrefix(array &$requestArguments): void
    {
        if (isset($requestArguments['metadataPrefix'])) {
            if (!array_key_exists($requestArguments['metadataPrefix'], $this->oaiConfiguration['metadata_formats'])) {
                throw new OaiException(sprintf('Bad argument. metadataPrefix %s', $requestArguments['metadataPrefix']), 1478852962);
            }
        } else {
            throw new OaiException(sprintf('Bad argument. metadataPrefix %s', ''), 1478853001);
        }
    }

    /**
     * @param array $requestArguments
     *
     * @return array
     *
     * @throws FileNotFoundException
     * @throws OaiException
     */
    private function parseArguments(array $requestArguments): array
    {
        $errors = [];
        unset($requestArguments['id']);

        //prepare answer
        if (is_array($requestArguments)) {
            foreach ($requestArguments as $key => $val) {
                if ('from' === $key || 'until' === $key) {
                    if (10 !== strlen($val)) {
                        continue;
                    }
                    $test = date_parse($val);
                    if (!$test || count($test['errors'])) {
                        continue;
                    }
                }
                if (in_array($key, $this->oaiConfiguration['request_attributes'])) {
                    if ('verb' === $key && !array_key_exists($val, $this->oaiConfiguration['verbs'])) {
                        continue;
                    }
                    $this->request->setAttribute($key, $val);
                }
                if (array_key_exists($val, $this->oaiConfiguration['verbs'])) {
                    $this->request->setAttribute($key, $val);
                }
            }
        }
        $this->oai_pmh->appendChild($this->request);

        //same argument
        if ($this->requestStack->getMasterRequest()->isMethod(Request::METHOD_GET)) {
            $requestQuery = $this->requestStack->getMasterRequest()->getQueryString() ?: '';
        } else {
            if ($this->requestStack->getMasterRequest()->isMethod(Request::METHOD_POST)) {
                $requestQuery = file_get_contents('php://input');
            } else {
                $requestQuery = '';
            }
        }

        $attributeCounter = $this->requestStack->getMasterRequest()->query->count();
        $requestQueryElements = explode('&', $requestQuery);
        if (isset($requestQueryElements) && count($requestQueryElements) > 1) {
            if (count($requestQueryElements) !== $attributeCounter) {
                foreach ($GLOBALS['_'.$_SERVER['REQUEST_METHOD']] as $key => $val) {
                    $arrKey = array_search($key.'='.$val, $requestQueryElements);
                    if (false !== $arrKey) {
                        unset($requestQueryElements[$arrKey]);
                    }
                }
                foreach ($requestQueryElements as $val) {
                    $_arrTmp = explode('=', $val);
                    $errors[$_arrTmp[0]] = $_arrTmp[1];
                    throw new OaiException(sprintf('Bad argument %s', $errors), 1478853319);
                }
            }
        }
        if (!isset($requestArguments['verb'])) {
            $requestArguments['verb'] = 'ListMetadataFormats';
        }
        //No verb
        if (0 === count($requestArguments) || !isset($requestArguments['verb'])) {
            throw new OaiException(sprintf('Bad verb NOVERB: %s', ''), 1478853352);
        }

        //resumptionToken is an exclusive argument, so get all necessary args from token
        //or stop all other action
        if (is_array($requestArguments) && isset($requestArguments['resumptionToken'])) {
            if ((2 === count($requestArguments) && !isset($requestArguments['verb'])) || count($requestArguments) > 2) {
                $requestQueryElements = $requestArguments;
                unset($requestQueryElements['resumptionToken']);
                throw new OaiException(sprintf('Bad argument %s', $requestQueryElements), 1478853579);
            }
            $this->restoreArgs($requestArguments);
        }
        if (isset($requestArguments['verb'])) {
            if (!array_key_exists($requestArguments['verb'], $this->oaiConfiguration['verbs'])) {
                throw new OaiException(sprintf('Bad verb %s: $s', $requestArguments['verb'], ''), 1478853608);
            }

            return $requestArguments;
        }

        return $requestArguments;
    }

    /**
     * @param string $verb
     * @param array  $requestArguments
     *
     * @throws OaiException
     */
    private function errorAllowedArguments(string $verb, array $requestArguments)
    {
        foreach ($requestArguments as $key => $val) {
            if ('verb' !== $key && !@in_array($key, explode(',', $this->oaiConfiguration['verbs'][$verb]['allowedArguments']))) {
                throw new OaiException(sprintf('Bad argument: %s: %s', $key, $val), 1478853155);
            }
        }
    }

    /**
     * @param string $verb
     * @param array  $requestArguments
     *
     * @return bool
     *
     * @throws OaiException
     */
    private function errorRequiredArguments(string $verb, array $requestArguments): bool
    {
        $requiredArguments = explode(',', $this->oaiConfiguration['verbs'][$verb]['requiredArguments']);
        unset($requestArguments['verb']);
        foreach ($requiredArguments as $key => $requiredArgument) {
            if (isset($requestArguments[$requiredArgument])) {
                unset($requiredArguments[$key]);
                reset($requiredArguments);
            }
        }
        if (count($requiredArguments)) {
            $noerror = false;
            foreach ($requiredArguments as $requiredArgument) {
                throw new OaiException(sprintf('Bad argument: %s: %s', $requiredArgument, ''), 1478853229);
            }
        } else {
            $noerror = true;
        }

        return $noerror;
    }

    /**
     * @param array $requestArguments
     *
     * @return bool
     *
     * @throws OaiException
     * @throws FileNotFoundException
     */
    private function restoreArgs(array &$requestArguments): bool
    {
        $strToken = $this->oaiTempDirectory->read('/oai-gdz/'.$requestArguments['resumptionToken']);

        try {
            parse_str($strToken, $arrToken);
            $requestArguments = array_merge($requestArguments, $arrToken);
            unset($requestArguments['resumptionToken']);
        } catch (FileNotFoundException $e) {
            throw new OaiException(sprintf('Bad Resumption Token %s.', $requestArguments['resumptionToken']), 1478853790);
        }

        return true;
    }

    /**
     * @param array $arr
     *
     * @return array $arrResult
     *
     * @throws OaiException
     */
    private function getRecords(array &$arr): array
    {
        $arrResult = [];
        $direction = true;
        $arr['maxresults'] = $this->oaiConfiguration['max_records'][$arr['metadataPrefix'].':'.$arr['verb']];

        if (!isset($arr['start'])) {
            $arr['start'] = 0;
        } else {
            $arr['start'] += $arr['maxresults'];
        }
        $arrResult['header'] = [];
        $arrResult['records'] = [];
        if (isset($arr['identifier'])) {
            $identifier = str_replace(
                $this->oaiConfiguration['oai_identifier']['scheme'].$this->oaiConfiguration['oai_identifier']['delimiter'].$this->oaiConfiguration['oai_identifier']['repositoryIdentifier'].$this->oaiConfiguration['oai_identifier']['delimiter'],
                '',
                trim($arr['identifier'])
            );
            $addWhere = ' (id:"'.$identifier.'")';
        } else {
            $addWhere = '';
            if (isset($arr['from']) || isset($arr['until'])) {
                if (!isset($arr['from'])) {
                    $from = new \DateTime('1970-01-01T00:00:00Z');
                    $direction = true;
                } else {
                    $from = new \DateTime($arr['from']);
                    $direction = false;
                }
                if (!isset($arr['until'])) {
                    $until = new \DateTime('9999-12-31T00:00:00Z');
                } else {
                    $until = new \DateTime($arr['until']);
                    $direction = false;
                }
                $addWhere .= ' (date_indexed:['.$from->format('Y-m-d\TH:i:s\Z').' TO '.$until->format('Y-m-d\TH:i:s\Z').'])';
            }
            if (isset($arr['set'])) {
                $arrTmp = explode('_', trim($arr['set']));
                for ($i = 1, $iMax = count($arrTmp); $i < $iMax; $i += 2) {
                    $addWhere .= ' ('.$arrTmp[$i - 1].':'.$arrTmp[$i].')';
                }
                unset($arrTmp);
            }
        }
        if ($this->oaiConfiguration['hide_collections']) {
            $addWhere .= '';
            foreach ($this->oaiConfiguration['hidden_collections'] as $dc) {
                $addWhere .= ' NOT(dc:'.$dc.')';
            }
            $addWhere .= '';
        }
        if ($arr['metadataPrefix']) {
            $mPrefix = $arr['metadataPrefix'];
        } else {
            $mPrefix = 'oai_dc';
        }

        $res = $this->query($this->oaiConfiguration['query_parameters'][$mPrefix].$addWhere, $this->oaiConfiguration['date_indexed_field'], $direction, $arr);
        $arrResult['hits'] = $res->getFoundCount();

        if (0 === $arrResult['hits']) {
            if ('GetRecord' === $arr['verb']) {
                throw new OaiException(sprintf('Id %s does not exist. Bad argument: identifier: %s', $identifier, $identifier), 1478853965);
            }
        }

        for ($i = 0, $iMax = min($arrResult['hits'], $arr['maxresults'], count($res->getDocuments())); $i < $iMax; ++$i) {
            /** @var Document $document */
            $document = $res->getDocument($i);

            $arrResult['header'][$i]['identifier'] = $this->oaiConfiguration['oai_identifier']['scheme'].$this->oaiConfiguration['oai_identifier']['delimiter'].$this->oaiConfiguration['oai_identifier']['repositoryIdentifier'].$this->oaiConfiguration['oai_identifier']['delimiter'].$document->getId();
            $arrResult['header'][$i]['datestamp'] = $document->getMetadata()['date_indexed'];

            foreach ($document->getClassification() as $setSpec) {
                if ($setSpec) {
                    if (isset($this->oaiConfiguration['sets']['dc_'.strtolower($setSpec)])) {
                        $arrResult['header'][$i]['setSpec'][] = 'dc_'.$setSpec;
                    }
                }
            }
            if (array_key_exists('setSpec', $arrResult['header'][$i]) && isset($arr['set']) && !in_array($arr['set'], $arrResult['header'][$i]['setSpec']) && isset($arrResult['header'][$i]['setSpec'])) {
                array_unshift($arrResult['header'][$i]['setSpec'], $arr['set']);
            }
            if (isset($arrResult['header'][$i]['setSpec'])) {
                $arrResult['header'][$i]['setSpec'] = array_unique($arrResult['header'][$i]['setSpec']);
            }
            if ('ListRecords' === $arr['verb'] || 'GetRecord' === $arr['verb']) {
                switch ($arr['metadataPrefix']) {
                    case 'oai_dc':
                        if (count($document->getParents()) > 0) {
                            $arrResult['metadata'][$i]['dc:relation'][0] = $document->getParents()[0]->getId();
                        }
                        $arrResult['metadata'][$i]['dc:title'][0] = $document->getTitle()[0];
                        $arrResult['metadata'][$i]['dc:creator'] = $document->getAuthors();
                        $arrResult['metadata'][$i]['dc:subject'] = $document->getClassification();
                        $arrResult['metadata'][$i]['dc:subject'][] = $document->getType();
                        $arrResult['metadata'][$i]['dc:language'] = $document->getLanguage();
                        $arrResult['metadata'][$i]['dc:publisher'] = $document->getPublisher();
                        $arrResult['metadata'][$i]['dc:date'][0] = $document->getPublishingYear();
                        $arrResult['metadata'][$i]['dc:type'][0] = $this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$document->getType()];
                        $arrResult['metadata'][$i]['dc:type'][1] = $this->oaiConfiguration['metadata_format_options']['oai_dc']['default']['dc:type'];
                        $arrResult['metadata'][$i]['dc:format'][0] = 'image/jpeg';
                        $arrResult['metadata'][$i]['dc:format'][1] = 'application/pdf';
                        $arrResult['metadata'][$i]['dc:identifier'][0] = 'http://resolver.sub.uni-goettingen.de/purl?'.$document->getId();
                        foreach ($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'] as $key => $val) {
                            $metadata = $document->getMetadata();
                            if (isset($metadata[$key])) {
                                $arrResult['metadata'][$i]['dc:identifier'][] = trim($val) . ' ' . $metadata[$key];
                            }
                        }
                        foreach ($document->getAdditionalIdentifiers() as $key => $val) {
                            if ($val && isset($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$key])) {
                                $arrResult['metadata'][$i]['dc:identifier'][] = trim($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$key]) . ' ' . trim($val);
                            }
                        }
                        //Zeitschriftenband
                        // dc:source Publisher: Titel. Ort Erscheinungsjahr.
                        if (2 === count($document->getParents())) {
                            $arrResult['metadata'][$i]['dc:source'][0] = implode('; ', $document->getPublisher()).': '.$document->getTitle()[0].'. '.implode('; ', $document->getPublishingPlaces()).' '.$document->getPublishingYear();
                        } else {
                            if (count($document->getParents()) > 2) {
                                // dc:source Autor: Zeitschrift. Band Erscheinungsjahr.
                                $arrResult['metadata'][$i]['dc:source'][0] = trim(implode('; ', $document->getAuthors()).': '.$document->getTitle()[0].'. '.$document->getPublishingYear());
                            }
                        }
                        break;
                    case 'mets':
                        $filename = sprintf('https://gdz.sub.uni-goettingen.de/mets/%s.mets.xml', $document->getId());
                        $arrResult['metadata'][$i]['mets:mets'] = file_get_contents($filename);
                        break;
                }
            }
        }
        //new ResumtionToken ?
        if ('ListRecords' === $arr['verb'] || 'ListIdentifiers' === $arr['verb']) {
            if (($arrResult['hits'] - $arr['start']) >= $arr['maxresults']) {
                $arrResult['token'] = 'oai_'.md5(uniqid((string) rand(), true));
                $strToken = '';
                //allowed keys
                $arrAllowed = ['from', 'until', 'metadataPrefix', 'set', 'resumptionToken', 'start'];
                foreach ($arr as $key => $val) {
                    if (in_array($key, $arrAllowed)) {
                        $strToken .= $key.'='.$val.'&';
                    }
                }
                $strToken .= 'hits='.$arrResult['hits'];
                $this->oaiTempDirectory->createDir('oai-gdz');
                $this->oaiTempDirectory->put('/oai-gdz/'.$arrResult['token'], $strToken);
            } else {
                unset($arrResult['token']);
            }
        }

        return $arrResult;
    }

    /**
     * @param string $query
     * @param string $sort
     * @param bool   $reverse
     * @param array  $configuration
     *
     * @return array
     */
    private function query(string $query, string $sort = 'date_indexed', bool $reverse = false, array $configuration = []): Results
    {
        $rows = $configuration['maxresults'] ?? 10;
        $start = $configuration['start'] ?? 0;
        $direction = $reverse ? 'desc' : 'asc';
        $query .= ' -doctype:fulltext';

        $solrQuery = $this->client
            ->createSelect()
            ->addSort($sort, $direction)
            ->setStart($start)
            ->setRows($rows)
            ->setQuery($query);

        $solrResults = $this->client
            ->select($solrQuery);

        $results = new Results();
        $results->setFoundCount($solrResults->getNumFound());

        foreach ($solrResults->getDocuments() as $solrDocument) {
            try {
                $document = $this->translator->getDocumentById($solrDocument['id']);
            } catch (\Throwable $t) {
                continue;
            }
            $document->addMetadata('date_indexed', $solrDocument['date_indexed']);
            $results->addDocument($document);
        }

        return $results;
    }

    /**
     * @param array $result
     * @param array $requestArguments
     *
     * @return \DOMElement
     */
    private function getResumptionToken(array $result, array $requestArguments): \DOMElement
    {
        $token = $result['token'] ?? '';

        $resumptionToken = $this->oai->createElement('resumptionToken', $token);
        $resumptionToken->setAttribute('expirationDate', (gmdate('Y-m-d\TH:i:s\Z', (time() + $this->oaiConfiguration['expiration_date']))));
        $resumptionToken->setAttribute('completeListSize', (string) $result['hits']);
        $resumptionToken->setAttribute('cursor', (string) $requestArguments['start']);

        return $resumptionToken;
    }

    private function createRootElement()
    {
        $this->oai_pmh = $this->oai->createElement('OAI-PMH');
        foreach ($this->oaiConfiguration['oai_pma'] as $key => $value) {
            $this->oai_pmh->setAttribute($key, $value);
        }

        $this->oai->appendChild($this->oai_pmh);

        $responseDate = $this->oai->createElement('responseDate', gmdate('Y-m-d\TH:i:s\Z', time()));
        $this->oai_pmh->appendChild($responseDate);

        $this->request = $this->oai->createElement('request', 'https://gdz.sub.uni-goettingen.de/oai2/');
    }

    private function listOaiDcRecords(array $result, $key, \DOMElement $metadata)
    {
        $this->oai_dc = $this->oai->createElement('oai_dc:dc');
        foreach ($this->oaiConfiguration['metadata_format_options']['oai_dc']['dc'] as $attribute => $value) {
            $this->oai_dc->setAttribute($attribute, $value);
        }
        $metadata->appendChild($this->oai_dc);
        foreach ($result['metadata'][$key] as $elementName => $elementValue) {
            if ($elementValue) {
                foreach ($elementValue as $_v) {
                    if ($_v) {
                        if (is_array($_v)) {
                            $_v = implode(' ', $_v);
                        }
                        if ('dc:description' === $elementName) {
                            $data = $this->oai->createCDATASection($_v);
                        } else {
                            $data = new \DOMText((string) $_v);
                        }
                        $node = $this->oai->createElement($elementName);
                        $node->appendChild($data);
                        $this->oai_dc->appendChild($node);
                    }
                }
            }
        }
    }

    private function listMetsRecords(array $result, $key, \DOMElement $metadata)
    {
        foreach ($result['metadata'][$key] as $elementName => $elementValue) {
            $tmp = new \DOMDocument();
            $test = $tmp->loadXML($elementValue);
            if ($test) {
                $mets = $tmp->getElementsByTagName('mets')->item(0);
                $import = $this->oai->importNode($mets, true);
                $metadata->appendChild($import);
            }
        }
    }

    /**
     * @param $requestArguments
     *
     * @throws OaiException
     */
    private function getListRecordsAndListIdentifiers($requestArguments): void
    {
        //error handling
        $this->errorDate($requestArguments);
        $this->errorMetaDataPrefix($requestArguments);
        $this->errorFromUntil($requestArguments);
        $result = $this->getRecords($requestArguments);
        if (0 === count($result)) {
            throw new OaiException('No matching records', 1478853689);
        }

        $listRecordsElement = $this->oai->createElement($requestArguments['verb']);
        $this->oai_pmh->appendChild($listRecordsElement);
        foreach ($result['header'] as $key => $val) {
            if ('ListRecords' === $requestArguments['verb']) {
                $this->record = $this->oai->createElement('record');
                $listRecordsElement->appendChild($this->record);
                $this->head = $this->oai->createElement('header');
                $this->record->appendChild($this->head);
            } else {
                $this->head = $this->oai->createElement('header');
                $listRecordsElement->appendChild($this->head);
            }
            foreach ($val as $elementName => $elementValue) {
                if (is_array($elementValue)) {
                    foreach ($elementValue as $_v) {
                        $node = $this->oai->createElement($elementName);
                        $node->appendChild(new \DOMText($_v));
                        $this->head->appendChild($node);
                    }
                } else {
                    $node = $this->oai->createElement($elementName);
                    $node->appendChild(new \DOMText((string) $elementValue));
                    $this->head->appendChild($node);
                }
            }
            if ('ListRecords' === $requestArguments['verb']) {
                $this->listRecords($requestArguments, $result, $key);
            }
        }

        $listRecordsElement->appendChild($this->getResumptionToken($result, $requestArguments));
    }

    /**
     * @param $requestArguments
     *
     * @return mixed
     *
     * @throws OaiException
     */
    private function getRecord($requestArguments)
    {
        $this->errorMetaDataPrefix($requestArguments);

        $result = $this->getRecords($requestArguments);

        if (!$result['hits']) {
            throw new OaiException('No Records Match', 1478853666);
        }

        $listRecordsElement = $this->oai->createElement($requestArguments['verb']);
        $this->oai_pmh->appendChild($listRecordsElement);
        foreach ($result['header'] as $key => $val) {
            $this->record = $this->oai->createElement('record');
            $listRecordsElement->appendChild($this->record);
            $this->head = $this->oai->createElement('header');
            $this->record->appendChild($this->head);
            foreach ($val as $elementName => $elementValue) {
                if (is_array($elementValue)) {
                    foreach ($elementValue as $_v) {
                        $node = $this->oai->createElement($elementName);
                        $node->appendChild(new \DOMText($_v));
                        $this->head->appendChild($node);
                    }
                } else {
                    $node = $this->oai->createElement($elementName);
                    $node->appendChild(new \DOMText((string) $elementValue));
                    $this->head->appendChild($node);
                }
            }
            $metadataElement = $this->oai->createElement('metadata');
            $this->record->appendChild($metadataElement);
            switch ($requestArguments['metadataPrefix']) {
                case 'oai_dc':
                    $this->oai_dc = $this->oai->createElement('oai_dc:dc');
                    foreach ($this->oaiConfiguration['metadata_format_options']['oai_dc']['dc'] as $attribute => $value) {
                        $this->oai_dc->setAttribute($attribute, $value);
                    }
                    $metadataElement->appendChild($this->oai_dc);

                    foreach ($result['metadata'][$key] as $elementName => $elementValue) {
                        if ($elementValue) {
                            foreach ($elementValue as $_v) {
                                if ($_v) {
                                    if (is_array($_v)) {
                                        $_v = implode(' ', $_v);
                                    }
                                    if ('dc:description' === $elementName) {
                                        $data = $this->oai->createCDATASection($_v);
                                    } else {
                                        $data = new \DOMText((string) $_v);
                                    }
                                    $node = $this->oai->createElement($elementName);
                                    $node->appendChild($data);
                                    $this->oai_dc->appendChild($node);
                                }
                            }
                        }
                    }
                    break;
                case 'mets':
                    foreach ($result['metadata'][$key] as $elementName => $elementValue) {
                        $tmp = new \DOMDocument();
                        $test = $tmp->loadXML($elementValue);
                        if ($test) {
                            $mets = $tmp->getElementsByTagName('mets')->item(0);
                            $import = $this->oai->importNode($mets, true);
                            $metadataElement->appendChild($import);
                        }
                    }
                    break;
            }
        }

        return $requestArguments;
    }
}
