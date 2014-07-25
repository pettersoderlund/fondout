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
                'name' => 'category',
                'options' => array(
                    'target_class'   => 'Fund\Entity\AccusationCategory',
                    'property'       => 'name',
                    'label_attributes' => array('class' => 'checkbox')
                ),
            )
        );
        // $this->add(new Element\Csrf('security'));
        // $this->add(array(
        //     'name' => 'send',
        //     'type'  => 'Submit',
        //     'attributes' => array(
        //         'value' => 'Submit',
        //     ),
        // ));



        // echo '<pre>';
        // \Doctrine\Common\Util\Debug::dump($this->get('category'));
        // echo '</pre>';
        // die;
//         $element->setValueOptions(array(
//     1 => array(
//         'label' => 'First Option',
//         'label_attributes' => array('class' => 'checkbox'),
//     ),
// ));

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Filter',
                    'class' => 'btn btn-success'
                )
            )
        );

        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}
