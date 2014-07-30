<?php

namespace Fund\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FundPageFilterForm extends Form implements ObjectManagerAwareInterface
{
    protected $objectManager;

    public function init()
    {
        $this->add(
            array(
                'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                'name' => 'category_visible',
                'options' => array(
                    'object_manager' => $this->getObjectManager(),
                    'target_class'   => 'Fund\Entity\AccusationCategory',
                    'property'       => 'name',
                ),
                'attributes' => array(
                    'multiple' => 'multiple',
                    'class' => 'form-control'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Visa vald kategori',
                    'class' => 'btn btn-xs'
                )
            )
        );
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
