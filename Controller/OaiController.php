<?php

declare(strict_types=1);

namespace Subugoe\OaiBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Subugoe\OaiBundle\Model\Identify\Identify;
use Subugoe\OaiBundle\Service\OaiServiceInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OaiController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OaiServiceInterface
     */
    private $oaiService;

    public function __construct(SerializerInterface $serializer, OaiServiceInterface $oaiService)
    {
        $this->serializer = $serializer;
        $this->oaiService = $oaiService;
    }

    /**
     * @Route("/oai2/")
     */
    public function indexAction(Request $request): Response
    {
        if ('ListSets' === $request->get('verb')) {
            return $this->forward('Subugoe\\OaiBundle\\Controller\\OaiController::listSets');
        }
        if ('ListMetadataFormats' === $request->get('verb')) {
            return $this->forward('Subugoe\\OaiBundle\\Controller\\OaiController::listMetadataFormats');
        }
        if ('Identify' === $request->get('verb')) {
            return $this->forward('Subugoe\\OaiBundle\\Controller\\OaiController::identify');
        }

        $response = new Response();
        $response->setContent($this->oaiService->start());
        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setStatusCode(Response::HTTP_OK);

        $this->oaiService->deleteExpiredResumptionTokens();

        return $response;
    }

    /**
     * @Route("/oai2/verb/Identify")
     */
    public function identify(Request $request)
    {
        $url = $request->getSchemeAndHttpHost().$request->getPathInfo();
        $oaiConfiguration = $this->getParameter('oai');
        $identify = $this->oaiService->getIdentify($url, $oaiConfiguration);

        $response = new Response();
        $response->setContent($this->serializer->serialize($identify, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * @Route("/oai2/verb/ListMetadataFormats")
     */
    public function listMetadataFormats(Request $request)
    {
        $url = $request->getSchemeAndHttpHost().$request->getPathInfo();
        $oaiConfiguration = $this->getParameter('oai');

        $metadataFormats = $this->oaiService->getMetadataFormats($url, $oaiConfiguration, $request->get('identifier'));

        $response = new Response();
        $response->setContent($this->serializer->serialize($metadataFormats, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * @Route("/oai2/verb/ListSets")
     */
    public function listSets(Request $request)
    {
        $url = $request->getSchemeAndHttpHost().$request->getPathInfo();
        $collections = $this->getParameter('collections');

        $sets = $this->oaiService->getListSets($url, $collections);
        $response = new Response();
        $response->setContent($this->serializer->serialize($sets, 'xml'));
        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }
}
