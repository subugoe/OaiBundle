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
        $url = $request->getSchemeAndHttpHost() . $request->getPathInfo();
        $oaiConfiguration = $this->getParameter('oai');
        $verb = $request->get('verb');
        switch ($verb) {
            case 'ListSets':
                $collections = $this->getParameter('collections');

                $content = $this->oaiService->getListSets($url, $collections);
                break;
            case 'Identify':
                $content = $this->oaiService->getIdentify($url, $oaiConfiguration);
                break;
            case 'ListMetadataFormats':
                $content = $this->oaiService->getMetadataFormats($url, $oaiConfiguration, $request->get('identifier'));
                break;
            default:
                $content = $this->oaiService->start();
                break;
        }


        $response = new Response();
        $content = $this->serializer->serialize($content, 'xml');
        $content = str_replace(
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet href="/bundles/subugoeoai/xsl/oai2.xsl" type="text/xsl"?>',
            $content
        );

        $response->setContent($content);

        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setStatusCode(Response::HTTP_OK);

        $this->oaiService->deleteExpiredResumptionTokens();

        return $response;
    }
}
