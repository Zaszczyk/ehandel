<?php

use Phalcon\Mvc\Model;

/**
 * Products
 */
class Products extends Model{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $price;


    /**
     * Products initializer
     */
    public function initialize(){
        $this->belongsTo('product_types_id', 'ProductTypes', 'id', array('reusable' => true));
    }

    /**
     * Returns a human representation of 'active'
     *
     * @return string
     */
    public function getActiveDetail(){
        if($this->active == 'Y'){
            return 'Yes';
        }

        return 'No';
    }

}
