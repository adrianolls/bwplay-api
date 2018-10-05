<?php

namespace App\RPC\Structs;

class GRoleInventory
{

    public $id;
    public $pos;
    public $count;
    public $max_count;
    public $data;
    public $proctype;
    public $expire_date;
    public $guid1;
    public $guid2;
    public $mask;

    public function __construct($item = null)
    {
        if ($item != null)
        {
            ;
        }
    }

}
