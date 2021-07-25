<?php


namespace App\Controller;

use App\Core\Database;
use App\Core\Email;
use App\Core\Helpers;
use App\Core\View;
use App\Core\FormValidator;
use App\Models\Orders;
use App\Models\Product_order;
use App\Models\Product_term;
use App\Models\User;
use App\Core\Security;

session_start();

class Commande
{

    public function listeCommandeAction(){

        Security::auth('orders');

        $view = new View("commandeList.back", "back");
        $view->assign("title", "Liste des commandes");
        $commande = new Product_order();

        $listOrders = $commande->select('*, COUNT('.DBPREFIXE.'product_order.id) as nbArticle, '.DBPREFIXE.'orders.status as idStatus ')
            ->innerJoin(DBPREFIXE."orders", DBPREFIXE."product_order.id_order", "=", DBPREFIXE."orders.id")
            ->innerJoin(DBPREFIXE."user", DBPREFIXE."orders.User_id", "=", DBPREFIXE."user.id")
            ->groupBy("id_order")->get();
        $view->assign("array", $listOrders);

    }

    public function displayCommandeAction(){

        Security::auth('orders');

        if (isset($_GET['id']) && !empty($_GET['id'])) {

            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id")->setParams(['id' => $_GET['id']])->get();

            if (empty($checkId)){
                header("Location: /admin/liste-commande");
                exit();
            }

            $view = new View("displayCommande.back", "back");
            $view->assign("title", "Détail de la commande");

            $order = new Orders();

            $orders = $order->select(DBPREFIXE."product_order.id_group_variant, " .DBPREFIXE."orders.id, " .DBPREFIXE."orders.CreatedAt, " .DBPREFIXE."orders.status, " .DBPREFIXE."orders.montant, " .DBPREFIXE."user.firstname, " .DBPREFIXE."user.lastname, " .DBPREFIXE."user.email"  )
                ->innerJoin(DBPREFIXE."product_order",DBPREFIXE."orders.id","=",DBPREFIXE."product_order.id_order")
                ->innerJoin(DBPREFIXE."user",DBPREFIXE."orders.User_id","=",DBPREFIXE."user.id")
                ->where(DBPREFIXE."product_order.id_order = :id")->setParams(['id' => $_GET['id']])->get();

            $array = [];

            foreach ($orders as $value){
                $productTerm = new Product_term();
                array_push($array,$productTerm->select(DBPREFIXE."terms.name AS nameTerm, ".DBPREFIXE."group_variant.id, ".DBPREFIXE."products.name, ".DBPREFIXE."group_variant.price ")
                    ->innerJoin(DBPREFIXE."group_variant",DBPREFIXE."product_term.idGroup ","=",DBPREFIXE."group_variant.id")
                    ->innerJoin(DBPREFIXE."products",DBPREFIXE."product_term.idProduct ","=",DBPREFIXE."products.id")
                    ->innerJoin(DBPREFIXE."terms",DBPREFIXE."product_term.idTerm ","=",DBPREFIXE."terms.id")
                    ->where(DBPREFIXE."product_term.idGroup = :idGroup")->setParams(["idGroup" => $value['id_group_variant']])->get());
            }

            $view->assign("products",$array);
            $view->assign("order",$orders);

        }else{
            header("Location: /admin/liste-commande");
        }

    }

    public function cancelOrderFrontAction(){

        if (!Security::isConnected()){
            header("Location: /connexion");
            exit();
        }

        if (isset($_GET['id']) && !empty($_GET['id'])){

            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id","User_id = :idUser")->setParams(['id' => $_GET['id'], 'idUser' => $_SESSION['user']['id']])->get();


            if (empty($checkId)){
                header("Location: /admin/liste-commande");
                exit();
            }

            $user = new User();

            $commande = $order->select('*')->where("id = :id")->setParams(["id" => $_GET['id']])->get();
            $getUser = $user->select('*')->where("id = :id")->setParams(["id" => $commande[0]["User_id"]])->get();

            $order->populate($commande[0]);
            $order->setUserId($commande[0]['User_id']);
            $order->setStatus(-1);
            $order->save();

            //Email::sendEmail("C&C - Annulation de votre commande",$getUser[0]["email"], "Votre commande vient d'être annulée ", "http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']."/connexion","Mon compte", "/admin/liste-commande");
            // Rajouter header location
        }else{
            header("Location: /mes-commandes");
        }

    }

    public function cancelCommandeAction(){


        Security::auth('orders');


        require 'vendor/autoload.php';

        if (isset($_GET['id']) && !empty($_GET['id'])){

            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id")->setParams(['id' => $_GET['id']])->get();

            if (empty($checkId)){
                header("Location: /admin/liste-commande");
                exit();
            }

            $user = new User();

            $commande = $order->select('*')->where("id = :id")->setParams(["id" => $_GET['id']])->get();
            $getUser = $user->select('*')->where("id = :id")->setParams(["id" => $commande[0]["User_id"]])->get();

            if ($commande[0]['status'] == 1 || $commande[0]['status']  == 2){
                header('location:/admin/liste-commande');
                exit();
            }

            $order->populate($commande[0]);
            $order->setPaymentIntent($commande[0]['payment_intent']);
            $order->setUserId($commande[0]['User_id']);
            $order->setStatus(-1);
            $order->save();

            /*
             * Remboursement du montant de la commande Via Stripe
             */
           \Stripe\Stripe::setApiKey(PRIVATEKEYSTRIPE);

            $re = \Stripe\Refund::create([
                'payment_intent' => $commande[0]['payment_intent'],
            ]);

            Email::sendEmail("C&C - Annulation de votre commande",$getUser[0]["email"], "Votre commande vient d'être annulée ", 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']."/connexion","Mon compte", "/admin/liste-commande");

        }else{
            header("Location: /admin/liste-commande");
        }

    }

    public function ValidCommandeAction(){

        Security::auth('orders');

        if (isset($_GET['id']) && !empty($_GET['id'])){

            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id")->setParams(['id' => $_GET['id']])->get();

            if (empty($checkId)){
                header("Location: /admin/liste-commande");
                exit();
            }

            $user = new User();

            $commande = $order->select('*')->where("id = :id")->setParams(["id" => $_GET['id']])->get();
            $getUser = $user->select('*')->where("id = :id")->setParams(["id" => $commande[0]["User_id"]])->get();

            if ($commande[0]['status'] == -1 || $commande[0]['status']  == 2){
                header('location:/admin/liste-commande');
                exit();
            }

            $order->populate($commande[0]);
            $order->setPaymentIntent($commande[0]['payment_intent']);
            $order->setUserId($commande[0]['User_id']);
            $order->setStatus(1);
            $order->save();

            Email::sendEmail("C&C - Votre commande est prete !", $getUser[0]["email"], "Votre commande est prête ! <br> Vous pouvez venir la chercher en magasin", "http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']."/connexion","Mon compte", "/admin/liste-commande");

        }else{
            header("Location: /admin/liste-commande");
        }

    }

    public function DoneCommandeAction(){

        Security::auth('orders');

        if (isset($_GET['id']) && !empty($_GET['id'])){

            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id")->setParams(['id' => $_GET['id']])->get();

            if (empty($checkId)){
                header("Location: /admin/liste-commande");
                exit();
            }

            $user = new User();

            $commande = $order->select('*')->where("id = :id")->setParams(["id" => $_GET['id']])->get();
            $getUser = $user->select('*')->where("id = :id")->setParams(["id" => $commande[0]["User_id"]])->get();

            if ($commande[0]['status'] == -1){
                header('location:/admin/liste-commande');
                exit();
            }

            $order->populate($commande[0]);
            $order->setPaymentIntent($commande[0]['payment_intent']);
            $order->setUserId($commande[0]['User_id']);
            $order->setStatus(2);
            $order->save();

            Email::sendEmail("C&C - Votre commande a ete cloturer", $getUser[0]["email"], "Votre commande vient d'être cloturer <br> Merci et à bientôt !", "http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']."/connexion","Mon compte", "/admin/liste-commande");

        }else{
            header("Location: /admin/liste-commande");
        }

    }

// FRONT
    public function displayOrdersFrontAction(){

        if (!Security::isConnected()){
            header("Location: /connexion");
            exit();
        }

        $view = new View("displayOrders.front");
        $view->assign("title","Mes commandes");

        $order = new Orders();
        $orders = $order->select("montant,id,CreatedAt,status")->where("User_id = :id")->setParams(['id' => $_SESSION['user']['id']])->get();

        $view->assign("orders",$orders);
    }


    public function informationsOrderAction(){


        if (!Security::isConnected()){
            header("Location: /connexion");
            exit();
        }

        if (isset($_GET['id']) && !empty($_GET['id']) ){


            $order = new Orders();
            $checkId = $order->select('id')->where("id = :id","User_id = :idUser")->setParams(['id' => $_GET['id'], 'idUser' => $_SESSION['user']['id']])->get();

            if (empty($checkId)){
                header("Location: /mes-commandes");
                exit();
            }

            $view = new View("infosOrder.front");
            $view->assign("title","Ma commande");

            $order = new Orders();
            $orders = $order->select(DBPREFIXE."product_order.id_group_variant, " .DBPREFIXE."orders.id, " .DBPREFIXE."orders.CreatedAt, " .DBPREFIXE."orders.status, " .DBPREFIXE."orders.montant"  )
                ->innerJoin(DBPREFIXE."product_order",DBPREFIXE."orders.id","=",DBPREFIXE."product_order.id_order")
                ->where(DBPREFIXE."product_order.id_order = :id")->setParams(['id' => $_GET['id']])->get();

            $array = [];

            foreach ($orders as $value){
                $productTerm = new Product_term();
                array_push($array,$productTerm->select(DBPREFIXE."terms.name AS nameTerm, ".DBPREFIXE."group_variant.id, ".DBPREFIXE."products.name, ".DBPREFIXE."group_variant.price ")
                    ->innerJoin(DBPREFIXE."group_variant",DBPREFIXE."product_term.idGroup ","=",DBPREFIXE."group_variant.id")
                    ->innerJoin(DBPREFIXE."products",DBPREFIXE."product_term.idProduct ","=",DBPREFIXE."products.id")
                    ->innerJoin(DBPREFIXE."terms",DBPREFIXE."product_term.idTerm ","=",DBPREFIXE."terms.id")
                    ->where(DBPREFIXE."product_term.idGroup = :idGroup")->setParams(["idGroup" => $value['id_group_variant']])->get());
            }

            $view->assign("products",$array);
            $view->assign("order",$orders);

        }else{
            header("Location: /mes-commandes");
        }
    }
}