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

    public $name;

    public $description;

    public $price;

    public $currency;

    public function initialize()
    {
        $this->hasMany("id", "Orders", "product_id");
    }
}
