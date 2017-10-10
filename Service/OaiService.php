<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Service;

use Subugoe\OaiBundle\Exception\OaiException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Solarium\Client;
use Subugoe\IIIFBundle\Model\Document;
use Subugoe\IIIFBundle\Translator\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OaiService
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
     * OaiService constructor.
     *
     * @param RequestStack        $requestStack
     * @param Client              $client
     * @param FilesystemInterface $oaiTempDirectory
     * @param TranslatorInterface $translator
     * @param array               $config
     */
    public function __construct(RequestStack $requestStack, Client $client, FilesystemInterface $oaiTempDirectory, TranslatorInterface $translator, array $config)
    {
        $this->requestStack = $requestStack;
        $this->client = $client;
        $this->oaiTempDirectory = $oaiTempDirectory;
        $this->translator = $translator;
        $this->oaiConfiguration = $config;
    }

    public function start()
    {
        // create XML-DOM
        $this->oai = new \DOMDocument('1.0', 'UTF-8');

        //nice output format (linebreaks and tabs)
        $this->oai->formatOutput = false;

        //insert xsl
        $this->oai->appendChild($this->oai->createProcessingInstruction('xml-stylesheet', 'href="/xsl/oai2.xsl" type="text/xsl"'));

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

        //#######################################################################################
        //#### GetRecord ########################################################################
        //#######################################################################################
        if ('GetRecord' === $requestArguments['verb']) {
            //error handling
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

            //getRecord
        }
        //#######################################################################################
        //#### END GetRecord ####################################################################
        //#######################################################################################
        //#######################################################################################
        //#### ListRecords / LstIdentifiers #####################################################
        //#######################################################################################
        if ('ListRecords' === $requestArguments['verb'] || 'ListIdentifiers' === $requestArguments['verb']) {
            //error handling
            $this->errorDate($requestArguments);
            $this->errorMetaDataPrefix($requestArguments);
            $this->errorFromUntil($requestArguments);
            $result = $this->getRecords($requestArguments);
            if (0 === count($result)) {
                throw new OaiException('No Records Match', 1478853689);
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

        return $this->oai->saveXML();
    }

    protected function listRecords(array $requestArguments, array $result, $key)
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
                if ('' !== $regs[1] && '' !== $regs[4]) {
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
    private function errorFromUntil(array &$requestArguments)
    {
        if (isset($requestArguments['from']) && isset($requestArguments['until'])) {
            if ((strlen($requestArguments['from'])) !== (strlen($requestArguments['until']))) {
                throw new OaiException(sprintf('Bad argument. from: %s until %s', $requestArguments['from'], $requestArguments['until']), 1478852818);
            } else {
                if (($requestArguments['from']) > ($requestArguments['until'])) {
                    throw new OaiException(sprintf('Bad argument. from: %s until %s', $requestArguments['from'], $requestArguments['until']), 1478852845);
                }
            }
        }
    }

    private function errorMetaDataPrefix(array &$requestArguments)
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
                throw new OaiException(sprintf('Bad argument: %: %', $key, $val), 1478853155);
            }
        }
    }

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
                throw new OaiException(sprintf('Bad argument: %: %', $requiredArgument, ''), 1478853229);
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
            $arr['start'] = $arr['start'] + $arr['maxresults'];
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
                    $from = '1970-01-01T00:00:00Z';
                    $direction = true;
                } else {
                    $from = $arr['from'];
                    $direction = false;
                }
                if (!isset($arr['until'])) {
                    $until = '9999-12-31T00:00:00Z';
                } else {
                    $until = $arr['until'];
                    $direction = false;
                }
                $addWhere .= ' (date_indexed:['.$from.' TO '.$until.'])';
            }
            if (isset($arr['set'])) {
                $arrTmp = explode('_', trim($arr['set']));
                for ($i = 1; $i < count($arrTmp); $i = $i + 2) {
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
        $arrResult['hits'] = count($res);

        if (0 === $arrResult['hits']) {
            if ('GetRecord' === $arr['verb']) {
                throw new OaiException(sprintf('Id %s does not exist. Bad argument: identifier: %s', $identifier, $identifier), 1478853965);
            }
        }

        for ($i = 0; $i < min($arrResult['hits'], $arr['maxresults']); ++$i) {
            /** @var Document $document */
            $document = $res[$i];

            $arrResult['header'][$i]['identifier'] = $this->oaiConfiguration['oai_identifier']['scheme'].$this->oaiConfiguration['oai_identifier']['delimiter'].$this->oaiConfiguration['oai_identifier']['repositoryIdentifier'].$this->oaiConfiguration['oai_identifier']['delimiter'].$document->getId();
            $arrResult['header'][$i]['datestamp'] = $document->getMetadata()['date_indexed'];

            foreach ($document->getClassification() as $setSpec) {
                if ($setSpec) {
                    if (isset($this->oaiConfiguration['sets']['dc_'.strtolower($setSpec)])) {
                        $arrResult['header'][$i]['setSpec'][] = 'dc_'.$setSpec;
                    }
                }
            }
            if (isset($arr['set']) && !in_array($arr['set'], $arrResult['header'][$i]['setSpec']) && isset($arrResult['header'][$i]['setSpec'])) {
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
                        $arrResult['metadata'][$i]['dc:date'][0] = $document->getPublishingPlaces();
                        $arrResult['metadata'][$i]['dc:type'][0] = $this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$document->getType()];
                        $arrResult['metadata'][$i]['dc:type'][1] = $this->oaiConfiguration['metadata_format_options']['oai_dc']['default']['dc:type'];
                        $arrResult['metadata'][$i]['dc:format'][0] = 'image/jpeg';
                        $arrResult['metadata'][$i]['dc:format'][1] = 'application/pdf';
                        $arrResult['metadata'][$i]['dc:identifier'][0] = 'http://resolver.sub.uni-goettingen.de/purl?'.$document->getId();
                        foreach ($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'] as $key => $val) {
                            $metadata = $document->getMetadata();
                            if (isset($metadata[$key])) {
                                array_push($arrResult['metadata'][$i]['dc:identifier'], trim($val).' '.$metadata[$key]);
                            }
                        }
                        foreach ($document->getAdditionalIdentifiers() as $key => $val) {
                            if ($val && isset($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$key])) {
                                array_push($arrResult['metadata'][$i]['dc:identifier'],
                                    trim($this->oaiConfiguration['metadata_format_options']['oai_dc']['identifier'][$key]).' '.trim($val));
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
                        $arrResult['metadata'][$i]['mets:mets'] = file_get_contents('http://gdz.sub.uni-goettingen.de/mets_export.php?PPN='.$document->getId());
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
    private function query(string $query, string $sort = 'date_indexed', bool $reverse = false, array $configuration = []): array
    {
        $rows = isset($configuration['maxresults']) ? $configuration['maxresults'] : 10;
        $start = isset($configuration['start']) ? $configuration['start'] : 0;
        $direction = $reverse ? 'desc' : 'asc';
        $query = $query.' -doctype:fulltext';

        $solrQuery = $this->client
            ->createSelect()
            ->addSort($sort, $direction)
            ->setStart($start)
            ->setRows($rows)
            ->setQuery($query);

        $solrResults = $this->client
            ->select($solrQuery)
            ->getDocuments();

        $documents = [];
        foreach ($solrResults as $solrDocument) {
            $document = $this->translator->getDocumentById($solrDocument['id']);
            $document->addMetadata('date_indexed', $solrDocument['date_indexed']);
            array_push($documents, $document);
        }

        return $documents;
    }

    /**
     * @param array $result
     * @param array $requestArguments
     *
     * @return \DOMElement
     */
    private function getResumptionToken(array $result, array $requestArguments): \DOMElement
    {
        $token = isset($result['token']) ? $result['token'] : '';

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

        $this->request = $this->oai->createElement('request', 'http://gdz.sub.uni-goettingen.de/oai2/');
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
}
