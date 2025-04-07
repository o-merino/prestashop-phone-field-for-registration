<?php
/**
*  @author Taoufiq Ait Ali
*/

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class Regrutfield extends Module
{
    public function __construct()
    {
        $this->name          = 'regrutfield';
        $this->tab           = 'front_office_features';
        $this->version       = '1.1.0';
        $this->author        = 'Taoufiq Ait Ali';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('rut field for registration');
        $this->description = $this->l('Add rut field to registration form.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }
    public function install()
    {
        $result = true;
        if (!parent::install()
            || !$this->registerHook('additionalCustomerFormFields')
            || !$this->registerHook('actionCustomerAccountAdd')
            || !$this->registerHook('actionAdminCustomersListingFieldsModifier')
            || !$this->registerHook('actionCustomerGridDefinitionModifier')
            || !$this->registerHook('actionCustomerGridQueryBuilderModifier')
        ) {
             $result = false;
        }
        $res = (bool)Db::getInstance()->execute(
            'ALTER TABLE `'._DB_PREFIX_.'customer`  
                ADD `rut` VARCHAR(64) NULL,
                ADD `phone` VARCHAR(64) NULL'
        );
        
        
        return $result;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()
        ) {
            return false;
        }
        $res = (bool)Db::getInstance()->execute(
            'ALTER TABLE `'._DB_PREFIX_.'customer` 
                DROP `rut`,
                DROP `phone`'
        );
        
        return true;
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $formFieldRut = new FormField();
        $formFieldRut->setName('rut')
                     ->setType('text')
                     ->setLabel($this->l('RUT'))
                     ->setRequired(false);
        
        $formFieldPhone = new FormField();
        $formFieldPhone->setName('phone')
                       ->setType('text')
                       ->setLabel($this->l('Teléfono'))
                       ->setRequired(false);
        
        return [$formFieldRut, $formFieldPhone];
        
    }
    
    public function hookActionCustomerAccountAdd($params)
    {   
        $customerId = (int)$params['newCustomer']->id;
        $rut = Tools::getValue('rut', '');
        $phone = Tools::getValue('phone', '');
        
        return Db::getInstance()->update(
            'customer',
            [
                'rut' => pSQL($rut),
                'phone' => pSQL($phone),
            ],
            'id_customer = ' . $customerId
        );
        
    }
    public function hookActionAdminCustomersListingFieldsModifier($params)
    {
        $params['fields']['rut'] = [
            'title' => $this->l('RUT'),
            'align' => 'center',
        ];
        
        $params['fields']['phone'] = [
            'title' => $this->l('Teléfono'),
            'align' => 'center',
        ];
        
    }

    
public function hookActionCustomerGridDefinitionModifier(array $params)
{
    /** @var GridDefinitionInterface $definition */
    $definition = $params['definition'];

    $definition->getColumns()->addAfter(
        'optin',
        (new DataColumn('rut'))
            ->setName($this->l('RUT'))
            ->setOptions(['field' => 'rut'])
    );
    
    $definition->getColumns()->addAfter(
        'rut',
        (new DataColumn('phone'))
            ->setName($this->l('Teléfono'))
            ->setOptions(['field' => 'phone'])
    );
    

    // For search filter
    $definition->getFilters()->add(
        (new Filter('rut', TextType::class))
        ->setAssociatedColumn('rut')
    );
    // For search filter
    $definition->getFilters()->add(
        (new Filter('phone', TextType::class))
        ->setAssociatedColumn('phone')
    );
}

	public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'IF(wcm.`rut` IS NULL, 0, wcm.`rut`) AS `rut`,' .
            'IF(wcm.`phone` IS NULL, 0, wcm.`phone`) AS `phone`'
        );
        

        $searchQueryBuilder->leftJoin(
            'c',
            '`' . pSQL(_DB_PREFIX_) . 'customer`',
            'wcm',
            'wcm.`id_customer` = c.`id_customer`'
        );

        if ('rut' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('wcm.`rut`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('rut' === $filterName) {
                $searchQueryBuilder->andWhere('wcm.`rut` = :rut');
                $searchQueryBuilder->setParameter('rut', $filterValue);

                if (!$filterValue) {
                    $searchQueryBuilder->orWhere('wcm.`rut` IS NULL');
                }
            }
        }
    }

}
