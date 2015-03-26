<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;

class OrdersController extends ControllerBase{
    public function initialize(){
        $this->tag->setTitle('Złóż zamówienie');
        parent::initialize();
    }

    public function newAction($productId = null){
        if($productId == null)
            $productId = $this->request->getPost('id');

        $product = Products::findFirst($productId)->toArray();
        $this->view->form = new OrdersForm(null, array('edit' => true, 'product' => $product));
    }


    /**
     * Creates a new company
     */
    public function createAction(){
        if(!$this->request->isPost()){
            return $this->forward("orders/index");
        }

        $form = new OrdersForm();
        $order = new Orders();

        $data = $this->request->getPost();
        if(!$form->isValid($data, $order)){
            foreach($form->getMessages() as $message){
                $this->flash->error($message);
            }

            return $this->forward('orders/new');
        }

        $date = new DateTime();
        $order->date = $date->format('Y-m-d H:i:s');
        $order->paid = 0;
        $order->hash = Phalcon\Text::random(Phalcon\Text::RANDOM_ALNUM, 32);

        if($order->save() == false){
            foreach($order->getMessages() as $message){
                $this->flash->error($message);
            }

            return $this->forward('orders/new');
        }

        $form->clear();

        $this->flash->success("Zamówienie zostało przyjęte, teraz możesz je opłacić w PayPal.");

        return $this->response->redirect("orders/payment/".$order->hash);
    }

    public function paymentAction($productHash = null){
        if($productHash == null){
            return $this->response->redirect('orders/new');
        }

        $order = Orders::findFirst('hash = "'.$productHash.'"')->toArray();
        if($order == null){
            return $this->response->redirect('orders/new');
        }

        $this->view->form = new PaymentForm(null, array('order' => $order));
    }

    public function payAction(){
        if(!$this->request->isPost()){
            return $this->response->redirect('orders/new');
        }
        $hash = $this->request->getPost('hash');

        $order = Orders::findFirst('hash = "'.$hash.'"');
        if($order == null){
            return $this->response->redirect('orders/new');
        }

        $this->_goToPaypal($order);
    }

    private function _goToPaypal($order){

        if($this->config->paypal->sandbox == true)
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        else
            $url = 'https://www.paypal.com/cgi-bin/webscr';

        $returnUrl = $this->config->application->path.$this->config->paypal->returnUrl;
        $cancelUrl = $this->config->application->path.$this->config->paypal->cancelUrl;
        $notifyUrl = $this->config->application->path.$this->config->paypal->notifyUrl;

        // Check if paypal request or response

        // Firstly Append paypal account to querystring
        $querystring = "?business=" . urlencode($this->di->get('config')['paypal']['email']) . "&";

        // Append amount& currency (L) to quersytring so it cannot be edited in html

        //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
        $querystring .= "item_name=" . urlencode($order->products->name) . "&";
        $querystring .= "amount=" . '99.90' . "&";
        $querystring .= "quantity=". '1'.'&';
        $querystring .= "currency_code=". urlencode($order->currency).'&';


        // Append paypal return addresses
        $querystring .= "return=" . urlencode(stripslashes($returnUrl)) . "&";
        $querystring .= "cancel_return=" . urlencode(stripslashes($cancelUrl)) . "&";
        $querystring .= "notify_url=" . urlencode($notifyUrl) . '&';
        $querystring .= "custom=" . $order->hash . '&';
        // Append querystring with custom field


        //loop for posted values and append to querystring
        foreach($_POST as $key => $value){
            $value = urlencode(stripslashes($value));
            $querystring .= "$key=$value&";
        }

        var_dump($this->config->paypal->sandbox);
        echo '<pre>';
        print_r($url . $querystring);
        echo '</pre>';
        // Redirect to paypal IPN
        header('Location: '.$url . $querystring);
    }
}
