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

        $res =(bool)Db::getInstance()->execute(
            'ALTER TABLE `'._DB_PREFIX_.'customer`  ADD `rut` varchar(64) NULL'
        );
        
        return $result;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()
        ) {
            return false;
        }
        $res =(bool)Db::getInstance()->execute(
            'ALTER TABLE `'._DB_PREFIX_.'customer` DROP `rut`'
        );
        return true;
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $formField = new FormField();
        $formField->setName('rut');
        $formField->setType('text');
        $formField->setLabel($this->l('rut'));
        $formField->setRequired(false);
        return array($formField);
    }
    
    public function hookActionCustomerAccountAdd($params)
    {   
        $customerId =$params['newCustomer']->id;
        $rut= Tools::getValue('rut','');
        return (bool) Db::getInstance()->execute('update '._DB_PREFIX_.'customer set rut=\''.pSQL($rut)."' WHERE id_customer=".(int) $customerId);
    }
    public function hookActionAdminCustomersListingFieldsModifier($params)
    {
        $params['fields']['rut'] = array(
            'title' => $this->l('rut'),
            'align' => 'center',
        );
    }

    
public function hookActionCustomerGridDefinitionModifier(array $params)
{
    /** @var GridDefinitionInterface $definition */
    $definition = $params['definition'];

    $definition
        ->getColumns()
        ->addAfter(
            'optin',
            (new DataColumn('rut'))
                ->setName($this->l('telerut'))
                ->setOptions([
                    'field' => 'rut',
                ])
        )
    ;

    // For search filter
    $definition->getFilters()->add(
        (new Filter('rut', TextType::class))
        ->setAssociatedColumn('rut')
    );
}

	public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'IF(wcm.`rut` IS NULL,0,wcm.`rut`) AS `rut`'
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
