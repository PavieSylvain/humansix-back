<?php

namespace App\Controller;

use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InsertXmlController extends AbstractController
{
    #[Route('/insert/xml', name: 'insert_xml')]
    public function index(OrderService $orderService): Response
    {
        /* init parameter */
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        /* get xml file & convert to array */
        $aOrders = $orderService->getXml();
        $res = $orderService->setData($aOrders);

        $response = new Response();

        if($res == true){
            var_dump("Insertion des données réussi");
        } else {
            var_dump("Echec de l'insertion des données");
        }
        return $response->send();
    }
}
