<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class PaymentForm extends Form{

    public function initialize($entity, $options = array()){

        $charset = new Hidden("charset");
        $charset->setAttribute('value', 'UTF-8');
        $this->add($charset);

        $cmd = new Hidden("cmd");
        $cmd->setAttribute('value', '_xclick');
        $this->add($cmd);

        $test_ipn = new Hidden("test_ipn");
        $test_ipn->setAttribute('value', '1');
        $this->add($test_ipn);

        $bn = new Hidden("bn");
        $bn->setAttribute('value', 'PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest');
        $this->add($bn);

        $hash = new Hidden("hash");
        $hash->setAttribute('value', $options['order']['hash']);
        $this->add($hash);

    }
}