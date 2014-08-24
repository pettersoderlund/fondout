<?php

namespace Fund\Form;

use Zend\Form\Form;

class FundPageFilterForm extends Form
{
    protected $objectManager;

    public function init()
    {
        $this->add(
            array(
                'type' => 'select',
                'name' => 'category_visible',
                'options' => array(
                    'empty_option'   => 'Välj kategori...'
                ),
                'attributes' => array(
                    'multiple' => 'multiple',
                    'class' => 'form-control',
                    'id' => 'filter-sustainability'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Filtrera',
                    'class' => 'btn btn-primary'
                )
            )
        );
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
