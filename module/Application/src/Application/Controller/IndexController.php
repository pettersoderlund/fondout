<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $container = new Container('fund');

        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\SustainabilityForm');

        $value = isset($container->sustainability) ? $container->sustainability : true;
        $form->get('sustainability')->setValue($value);

        return new ViewModel(
            array(
                'form' => $form
            )
        );
    }

    public function productsAction()
    {
      return new ViewModel(array());
  }

}
