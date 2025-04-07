<?php

class Customer extends CustomerCore
{
    /** @var string rut */
    public $rut;

    /** @var string phone */
    public $phone;

    public function __construct($idStore = null, $idLang = null)
    {
        self::$definition['fields']['rut'] = array(
            'type' => self::TYPE_STRING,
            'validate' => 'isGenericName',
            'required' => false,
            'size' => 64
        );

        self::$definition['fields']['phone'] = array(
            'type' => self::TYPE_STRING,
            'validate' => 'isGenericName',
            'required' => false,
            'size' => 64
        );

        parent::__construct($idStore, $idLang);
    }
}
