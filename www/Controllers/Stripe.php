<?php


namespace App\Controller;


use App\Core\View;
use App\Models\Group_variant;
use App\Models\Product_order;
use App\Models\Orders as Orders_model;

class Stripe
{
    function paymentStripeAction(){
        require 'vendor/autoload.php';
        session_start();
        
        \Stripe\Stripe::setApiKey('sk_test_51JC0puGueu1Z1r2SmxqKTcVKd7GHDBvZV0fPSbBI8GczQXd4y4bPAv5HgfMLJSy38vW6uyHwmN7bMrKUrIEw9sF400YiBrLMKe');

        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'http://localhost:8080';

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $_SESSION['panierTotal']*100,
                    'product_data' => [
                        'name' => 'Montant du panier',
                        'images' => [],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success',
            'cancel_url' => $YOUR_DOMAIN . '/cancel',
        ]);

        echo json_encode(['id' => $checkout_session->id]);
    }

    function successAction(){


        $view = new View("successStripe");
        session_start();


        $orders = new Orders_model();
        $orders->setUserId($_SESSION['user'][0]['id']);
        $orders->setMontant($_SESSION['panierTotal']);
        $orders->setStatus(0);
        $orders->save();

        $panier = New Orders_model();
        $panier = $orders->select('*')->where("montant = :montant", "status = 0", "User_id = :id")
            ->setParams(["montant" =>$_SESSION['panierTotal'], "id" => $_SESSION['user'][0]['id']])->get();
        $stock = new Group_variant();
        foreach ($_SESSION['panier'] as $key => $value) {

            for($i = 0; $i< intval($value); $i++ ){

                $stock = $stock->select('stock, price')->where("id = :id")->setParams(["id" => $key])->get();

                $_SESSION['errorPanier']  = null;

                if ($stock[0]['stock'] == "0"){
                    $_SESSION['errorPanier'] = "un ou plusieurs produit n'ont plus assez de stock ";
                    exit();
                }else{
                    $variant = new Group_variant();
                    $variant->setId($key);
                    $variant->setStock(intval($stock[0]['stock']) -1 );
                    $variant->setPrice(intval($stock[0]['price']) );
                    $variant->save();


                    $product = new Product_order();
                    $product->setIdGroupVariant($key);
                    $product->setIdOrder($panier[0]['id']);
                    $product->save();
                }

                $stock = new Group_variant();
            }
        }
        unset($_SESSION['panier']);
        unset($_SESSION['panierTotal']);

        $view->assign("title", "C&C - Succes du paiement");
    }
    function cancelAction(){
        $view = new View("cancelStripe");
        $view->assign("title", "C&C - Echec du paiement");
    }
    function pagePaiementStripeAction(){
        $view = new View("checkoutStripe");
        $view->assign("title", "C&C - Page de paiement");
    }

    function checkStockProductsAction(){
        session_start();
        $view = new View("cancelStripe");
        $view->assign("title", "C&C - Echec du paiement");

        $stocks = new Group_variant();
        $stock = new Group_variant();

        foreach ($_SESSION['panier'] as $key => $value) {
            $stocks = $stock->select('*')->where("id = :id")->setParams(["id" => $key])->get();
            //var_dump($stocks);
            $stock->populate($stocks[0]);

            for($i = 0; $i< intval($value); $i++ ) {
                $stock->setStock(intval($stock->getStock()) - 1);
                if ($stock->getStock() < 0){
                    http_response_code(400);
                    exit();
                }
            }
            http_response_code(200);
            $stock = new Group_variant();
            $stocks = new Group_variant();
        }
    }
}