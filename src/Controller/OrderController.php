<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends ApiController
{
    #[Route('/orders/add', name: 'orders_add')]
    public function add(Request $request, OrderService $orderService): Response
    {
        $request = $this->transformJsonBody($request);
        $order = $request->get("order");

        $isSuccess = $orderService->add($order);

        if($isSuccess){
            return $this->respondCreated(sprintf('Commande enregistré avec succes'));
        } else {
            return $this->respondValidationError("Echec de l'enregistrement de votre commande'");
        }
    }
}
