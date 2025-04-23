#importante ,para añadir al webservice se debe agregar en Override/classes/customer.php 

protected $webserviceParameters = [
    'fields' => [
        'rut' => ['required' => false],
        'phone' => ['required' => false],
    ],
];