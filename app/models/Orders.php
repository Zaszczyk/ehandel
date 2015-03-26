<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Orders extends Model{
    public function validation(){
        $this->validate(new EmailValidator(array('field' => 'email')));

        if($this->validationHasFailed() == true){
            return false;
        }
    }

    public function initialize()
    {
        $this->hasOne("product_id", "Products", "id");
    }
}
