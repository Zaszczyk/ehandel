<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\File as FileAdapter;

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
        $product = Products::findFirst($form->get('product_id'));

        $order->currency = $product->currency;
        $order->price = $product->price;

        $data = $this->request->getPost();
        if(!$form->isValid($data, $order)){
            echo '<pre>';
            print_r($form->getMessages());
            echo '</pre>';
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

        return $this->response->redirect("orders/payment/" . $order->hash);
    }

    public function paymentAction($productHash = null){
        if($productHash == null){
            return $this->response->redirect('orders/new');
        }

        $order = Orders::findFirst('hash = "' . $productHash . '"');

        if($order == null){
            return $this->response->redirect('orders/new');
        }

        $order = $order->toArray();

        $this->view->form = new PaymentForm(null, array('order' => $order));
    }

    public function payAction(){
        if(!$this->request->isPost()){
            return $this->response->redirect('orders/new');
        }
        $hash = $this->request->getPost('hash');

        $order = Orders::findFirst('hash = "' . $hash . '"');
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

        $returnUrl = $this->config->application->path . $this->config->paypal->returnUrl;
        $cancelUrl = $this->config->application->path . $this->config->paypal->cancelUrl;
        $notifyUrl = $this->config->application->path . $this->config->paypal->notifyUrl;

        // Check if paypal request or response

        // Firstly Append paypal account to querystring
        $querystring = "?business=" . urlencode($this->config->paypal->email) . "&";

        // Append amount& currency (L) to quersytring so it cannot be edited in html

        //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
        $querystring .= "item_name=" . urlencode($order->products->name) . "&";
        $querystring .= "amount=" . '99.90' . "&";
        $querystring .= "quantity=" . '1' . '&';
        $querystring .= "currency_code=" . urlencode($order->currency) . '&';


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

        $logger = new FileAdapter(APP_PATH."app/logs/request.log");
        $logger->error($querystring);

        header('Location: ' . $url . $querystring);
    }

    public function paypalProcessAction(){

        $loggerPP = new FileAdapter(APP_PATH."app/logs/request.log");
        $loggerPP->error(var_export($this->request->getPost(), true));

        if(!$this->request->hasPost('txn_id') || !$this->request->hasPost('txn_type'))
            return false;

        $data = $this->_payPalRequest();

        $loggerPP->error(var_export($data, true));

        try{
            if(strcmp(trim($data['res']), "VERIFIED") == 0){

                if($data['receiver_email'] != $this->config->paypal->email)
                    throw new \Exception('Nieprawidłowy adres email sprzedawcy.');

                $order = Orders::findFirst('hash = "' . $data['custom'] . '"');

                if($order->paid != 0)
                    throw new \Exception('Zamówienie zostało już opłacone wcześnie.');

                if($order->currency != $data['payment_currency'])
                    throw new \Exception('Nieprawidłowa waluta.');

                $price = number_format($data['payment_amount'], 2, '.', '');

                if($order->price != $price)
                    throw new \Exception('Nieprawidłowa waluta.');


                if($data['payment_status'] != 'Completed')
                    throw new \Exception('Parametr payment_status nie równa się Completed');

                $order->paid = 1;
                $order->txn_id = $data['txn_id'];
                $order->save();
            }

            elseif(strcmp(trim($data['res']), "INVALID") == 0){
                throw new \Exception('Płatność nieprawidłowa.');
            }
            else{
                throw new \Exception('Nieprawidłowe dane od PayPala.');

            }
        }
        catch(\Exception $e){
            $logger = new FileAdapter(APP_PATH."app/logs/paypal.log");
            $logger->error($e->getMessage());
        }
    }


    private function _payPalRequest(){
        $post = $this->request->getPost();

        if($this->config->paypal->sandbox == true)
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        else
            $url = 'https://www.paypal.com/cgi-bin/webscr';

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach($post as $key => $value){
            $value = urlencode(stripslashes($value));
            $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value);// IPN fix
            $req .= "&$key=$value";
        }

        // assign posted variables to local variables
        $data['item_name'] = $post['item_name'];
        $data['item_number'] = $post['item_number'];
        $data['payment_status'] = $post['payment_status'];
        $data['payment_amount'] = (float)$post['mc_gross'];
        $data['payment_currency'] = $post['mc_currency'];
        $data['txn_id'] = $post['txn_id'];
        $data['receiver_email'] = $post['receiver_email'];
        $data['payer_email'] = $post['payer_email'];
        $data['custom'] = $post['custom'];
        $data['mc_fee'] = $post['mc_fee'];

        $this->_Uid = $data['uid'];
        $ch = curl_init();

        $curlOptions = array(CURLOPT_URL => $url, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_VERBOSE => 1, CURLOPT_SSLVERSION => 4, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_RETURNTRANSFER => 1, CURLOPT_POSTFIELDS => $req, CURLOPT_POST => 1,);
        curl_setopt_array($ch, $curlOptions);


        $data['res'] = curl_exec($ch);

        if(curl_errno($ch)){
            $message = 'Połączenie curl nie działa - ' . var_export($data['res'], true) . ' - ' . var_export($data, true) . ' - ' . curl_error($ch) . '(' . curl_errno($ch) . ' - ' . curl_multi_strerror(curl_errno($ch)) . ')';

            $logger = new FileAdapter(APP_PATH."app/logs/errors.log");
            $logger->error($message);

        }
        else{
            curl_close($ch);
        }

        return $data;
    }

    public function paidAction(){
        $this->view->post = $this->request->getPost();
    }
}
