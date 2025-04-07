<?php
/**
 * Created by PhpStorm.
 * User: clever
 * Date: 14/12/18
 * Time: 11:03
 */

class Customer extends CustomerCore
{
    /** @var string rut */
    public $rut;
    
    public function __construct($idStore = null, $idLang = null)
    {
        Self::$definition['fields']['rut']=array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 64);
        parent::__construct($idStore, $idLang);
    }


}