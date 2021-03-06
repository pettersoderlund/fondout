<?php

namespace Fund\Form;

use Zend\Form\Element;
use Zend\Form\Form;

use Fund\Entity\AccusationCategory;

class FundFilterForm extends Form
{

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $this->add(
            array(
                'type' => 'objectmulticheckbox',
                'name' => 'fondoutcategory',
                'options' => array(
                    'target_class'   => 'Fund\Entity\FondoutCategory',
                    'property'       => 'title',
                    'find_method'    => array(
                        'name'   => 'getRootNodes',
                        'params' => array(
                            'sortByField' => 'title'
                            // 'criteria' => array('active' => 1),
                            // Use key 'orderBy' if using ORM
                            // 'orderBy'  => array('lastname' => 'ASC'),
                            // Use key 'sort' if using ODM
                            // 'sort'  => array('lastname' => 'ASC')
                        ),
                    ),
                    'label_attributes' => array('class' => 'checkbox')
                ),
            )
        );

        $this->add(
            array(
                'type' => 'objectselect',
                'name' => 'fund',
                'options' => array(
                    'target_class'   => 'Fund\Entity\Fund',
                    'property'       => 'name',
                    'display_empty_item' => true,
                    'empty_item_label'   => 'Välj fonder..',
                    'find_method'    => array(
                        'name'   => 'findAllFunds',
                        'params' => array(
                            /*'criteria' => array('active' => 1),*/
                            // Use key 'orderBy' if using ORM
                            // 'orderBy'  => array('lastname' => 'ASC'),
                        ),
                    ),
                ),
                'attributes' => array(
                    'multiple' => 'multiple',
                    'class' => 'form-control',
                    'id' => 'filter-fund'
                )
            )
        );

        $this->add(
            array(
                'type' => 'objectselect',
                'name' => 'company',
                'options' => array(
                    'target_class'   => 'Fund\Entity\FundCompany',
                    'property'       => 'name',
                    'display_empty_item' => true,
                    'empty_item_label'   => 'Välj fondbolag...',
                    'find_method'    => array(
                      'name'   => 'findActiveFundcompanies'
                    )
                ),
                'attributes' => array(
                    'multiple' => 'multiple',
                    'class' => 'form-control',
                    'id' => 'filter-company'
                )
            )
        );

        $this->add(
            array(
                'name' => 'size',
                'type' => 'multicheckbox',
                'options' => array(
                    'label' => 'Type',
                    'value_options' => array(
                        'small' => 'Liten',
                        'medium' => 'Mellan',
                        'large' => 'Stor',
                    ),
                )
            )
        );

        $this->add(
            array(
                'name' => 'sustainabilityscore',
                'type' => 'multicheckbox',
                'options' => array(
                    'label' => 'Type',
                    'value_options' => array(
                        '10' => '10',
                        '9' => '9',
                        '8' => '8',
                        '7' => '7',
                        '6' => '6',
                        '5' => '5',
                        '4' => '4',
                        '3' => '3',
                        '2' => '2',
                        '1' => '1'
                    ),
                )
            )
        );

        $this->add(
            array(
                'name' => 'category',
                'type' => 'hidden'
            )
        );

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Filtrera',
                    'class' => 'btn btn-primary btn-sm'
                )
            )
        );

        $this->add(
            array(
                'name' => 'q',
                'options' => array(),
                'attributes' => array(
                    'type'  => 'text',
                    'class' => 'form-control'
                )
            )
        );

        $this->add(
            array(
                'name' => 'qSubmit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Sök',
                    'class' => 'btn btn-primary'
                )
            )
        );

        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}
