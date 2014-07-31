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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'category_visible',
                'options' => array(
                    'label' => "Välj kategorier",
                    'object_manager' => $this->getObjectManager(),
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
                    'value' => 'Visa',
                    'class' => 'btn btn-primary btn-xs'
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

    public function setCategories(array $category)
    {
        $cat = array();
        // Format text CATEGORY (COUNT)
        foreach ($category as $categoryId => $categoryAttributes) {
            $cat[$categoryId] = $categoryAttributes[0] . ' ('
                . $categoryAttributes[1] . ')';
        }
        $this->get('category_visible')->setValueOptions($cat);
    }
}
