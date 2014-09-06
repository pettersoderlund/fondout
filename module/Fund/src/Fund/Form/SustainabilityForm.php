<?php

namespace Fund\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class SustainabilityForm extends Form
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
                'name' => 'sustainability',
                'options' => array(
                    'target_class'   => 'Fund\Entity\AccusationCategory',
                    'property'       => 'name',
                    'label_attributes' => array('class' => 'checkbox'),
                    'attributes' => array(
                        'checked' => 'checked'
                    )
                ),
            )
        );

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Filter',
                    'class' => 'btn btn-primary'
                )
            )
        );

        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}
