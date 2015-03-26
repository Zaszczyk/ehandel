<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class OrdersForm extends Form{

    public function initialize($entity, $options = array()){

        $id = new Hidden("product_id");
        $id->setAttribute('value', $options['product']['id']);
        $this->add($id);

        $productName = new Text("product");
        $productName->setLabel("Produkt");
        $productName->setFilters(array('striptags', 'string'));
        $productName->setAttribute('readonly', 'readonly');
        $productName->setAttribute('value', $options['product']['name']);
        $productName->addValidators(array(new PresenceOf(array('message' => 'Imię i nazwisko są wymagane.'))));
        $this->add($productName);

        $price = new Text("price");
        $price->setLabel("Cena");
        $price->setFilters(array('striptags', 'string'));
        $price->setAttribute('readonly', 'readonly');
        $price->setAttribute('value', $options['product']['price']);
        $this->add($price);

        $name = new Text("name");
        $name->setLabel("Imię i nazwisko");
        $name->setFilters(array('striptags', 'string'));
        $name->addValidators(array(new PresenceOf(array('message' => 'Imię i nazwisko są wymagane.'))));
        $this->add($name);

        $telephone = new Text("address");
        $telephone->setLabel("Adres");
        $telephone->setFilters(array('striptags', 'string'));
        $telephone->addValidators(array(new PresenceOf(array('message' => 'Adres jest wymagany'))));
        $this->add($telephone);

        $address = new Text("email");
        $address->setLabel("Adres e-mail");
        $address->setFilters(array('striptags', 'string'));
        $address->addValidators(array(new PresenceOf(array('message' => 'Adres e-mail jest wymagany'))));
        $this->add($address);

    }
}