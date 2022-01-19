<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;


class OrderService
{
    private CustomerRepository $customerRepository;
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CustomerRepository $customerRepository, OrderRepository $orderRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    public function getXml(){
                $sOrders=simplexml_load_string(file_get_contents("http://127.0.0.1:8000/orders.xml"));

        $json = json_encode($sOrders);

        $stdOrders = json_decode($json, true);

        return $stdOrders;
    }

    public function setData(array $aOrders): bool
    {
        $x = 1;
        foreach ($aOrders["order"] as $aOrder){
            $x++;

            $orderId = $aOrder["@attributes"]["id"];

            $dataOrder = $this->orderRepository->findOneBy(["ref" => $orderId]);

            if($dataOrder == null){
                $oOrder = new Order();

                $customer = $aOrder["customer"];
                $orderDate = $aOrder["orderDate"];
                $status = $aOrder["status"];
                $price = $aOrder["price"];
                $cartInit = $aOrder["cart"];
                $customerId = $customer["@attributes"]["id"];
                $dataCustomer = $this->customerRepository->findOneBy(["ref" => $customerId]);

                if($dataCustomer == null){
                    $oCustomer = new Customer();
                    $oCustomer->setFirstname($customer["firstname"]);
                    $oCustomer->setLastname($customer["lastname"]);
                    $oCustomer->setRef($customerId);

                    $this->entityManager->persist($oCustomer);
                } else {
                    $oCustomer = $dataCustomer;
                }

                $aCart = [];

                try {
                    $test = $cartInit["product"]["@attributes"]["sku"];
                } catch(\ErrorException $e){
                    $cartInit = $cartInit["product"];
                }

                foreach ($cartInit as $product){
                    $sku = $product["@attributes"]["sku"];
                    $name = $product["name"];
                    $quantity = $product["quantity"];
                    $priceProduct = $product["price"];

                    $dataProduct = $this->productRepository->findOneBy(["sku" => $sku]);
                    if($dataProduct == null){
                        $oProduct = new Product();
                        $oProduct->setName($name);
                        $oProduct->setPrice($priceProduct);
                        $oProduct->setSku($sku);

                        $this->entityManager->persist($oProduct);
                        $this->entityManager->flush();
                    } else {
                        $oProduct = $dataProduct;
                    }
                    $aCart[] = ["product" => $oProduct, "quantity" => $quantity];
                }

                $oOrder->setCustomer($oCustomer);
                $oOrder->setOrderDate(new \DateTime($orderDate));
                $oOrder->setPrice($price);
                $oOrder->setCart($aCart);
                $oOrder->setStatus($status);
                $oOrder->setRef($orderId);

                $this->entityManager->persist($oOrder);
                $this->entityManager->flush();
            }
        }

        return true;
    }

    public function add($order): bool
    {
        $cart = [];

        foreach ($order["cart"] as $element){
            $oProduct = $this->productRepository->findOneBy(["id" => $element["product"]["id"]]);
            $quantity = $element["quantity"];

            $cart[] = ["product" => $oProduct, "quantity" => $quantity];
        }

        $oOrder = new Order();
        $oOrder->setCustomer($this->customerRepository->findOneBy(["id" => $order["customer"]["id"]]));
        $oOrder->setStatus($order["status"]);
        $oOrder->setCart($cart);
        $oOrder->setOrderDate(new \DateTime());
        $oOrder->setPrice($order["price"]);

        $this->entityManager->persist($oOrder);

        try {
            $this->entityManager->flush();
            return true;
        } catch (\Doctrine\DBAL\Exception $e){
            return false;
        }
    }
}