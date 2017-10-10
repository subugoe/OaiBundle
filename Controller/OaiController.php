<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Controller;

use Subugoe\OaiBundle\Model\Collection;
use Subugoe\OaiBundle\Model\Identify\Description;
use Subugoe\OaiBundle\Model\Identify\Identification;
use Subugoe\OaiBundle\Model\Identify\Identify;
use Subugoe\OaiBundle\Model\Identify\OaiIdentifier;
use Subugoe\OaiBundle\Model\MetadataFormat;
use Subugoe\OaiBundle\Model\MetadataFormats;
use Subugoe\OaiBundle\Model\Sets;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OaiController extends Controller
{
    /**
     * @Route("/oai2/")
     */
    public function indexAction(Request $request): Response
    {
        if ('ListSets' === $request->get('verb')) {
            return $this->forward('SubugoeOaiBundle:Oai:listSets');
        }
        if ('ListMetadataFormats' === $request->get('verb')) {
            return $this->forward('SubugoeOaiBundle:Oai:listMetadataFormats');
        }
        if ('Identify' === $request->get('verb')) {
            return $this->forward('SubugoeOaiBundle:Oai:identify');
        }

        $response = new Response();
        $response->setContent($this->get('oai_service')->start());
        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setStatusCode(Response::HTTP_OK);

        $this->deleteExpiredResumptionTokens();

        return $response;
    }

    /**
     * @Route("/oai2/verb/Identify")
     */
    public function identifyAction(Request $request)
    {
        $identify = new Identify();
        $identification = new Identification();
        $description = new Description();
        $oaiIdentifier = new OaiIdentifier();
        $oaiIdentifierTags = $this->getParameter('oai')['oai_identifier'];

        $oaiIdentifier
            ->setNamespace($oaiIdentifierTags['xmlns'])
            ->setXsi($oaiIdentifierTags['xmlns_xsi'])
            ->setSchemaLocation($oaiIdentifierTags['xsi_schema_location'])
            ->setScheme($oaiIdentifierTags['scheme'])
            ->setDelimiter($oaiIdentifierTags['delimiter'])
            ->setRepositoryIdentifier($oaiIdentifierTags['repository_identifier'])
            ->setSampleIdentifier($oaiIdentifierTags['sample_identifier']);

        $description->setOaiIdentifier($oaiIdentifier);
        $identificationTags = $this->getParameter('oai')['identification_tags'];
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($request->getSchemeAndHttpHost().$request->getPathInfo())
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

        $response = new Response();
        $response->setContent($this->get('jms_serializer')->serialize($identify, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * @Route("/oai2/verb/ListMetadataFormats")
     */
    public function listMetadataFormatsAction(Request $request)
    {
        $metadataFormats = new MetadataFormats();
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($request->getSchemeAndHttpHost().$request->getPathInfo())
            ->setVerb('ListMetadataFormats');

        if ($request->get('identifier')) {
            $oaiRequest->setIdentifier($request->get('identifier'));
        }
        $metadataFormats->setDate(new \DateTimeImmutable())
            ->setRequest($oaiRequest);

        $formats = [];
        $availableFormats = $this->getParameter('oai')['metadata_formats'];
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

        $response = new Response();
        $response->setContent($this->get('jms_serializer')->serialize($metadataFormats, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * @Route("/oai2/verb/ListSets")
     */
    public function listSetsAction(Request $request)
    {
        $sets = new Sets();
        $oaiRequest = (new \Subugoe\OaiBundle\Model\Request())
            ->setUrl($request->getSchemeAndHttpHost().$request->getPathInfo())
            ->setVerb('ListSets');

        $sets->setDate(new \DateTimeImmutable())
            ->setRequest($oaiRequest);

        $collections = $this->getParameter('collections');
        $collectionStorage = [];
        foreach ($collections as $collection) {
            $collectionItem = new Collection();
            $collectionItem
                ->setId(sprintf('dc_%s', $collection['id']))
                ->setLabel($this->get('translator')->trans($collection['id']));
            $collectionStorage[] = $collectionItem;
        }
        $sets->setSets($collectionStorage);

        $response = new Response();
        $response->setContent($this->get('jms_serializer')->serialize($sets, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    private function deleteExpiredResumptionTokens()
    {
        $time = time() - 259200;
        $filesystem = $this->get('oaitemp_filesystem');
        $contents = $filesystem->listContents('/oai-gdz/');
        foreach ($contents as $object) {
            if ($object['timestamp'] < $time) {
                $filesystem->delete($object['path']);
            }
        }
    }
}
