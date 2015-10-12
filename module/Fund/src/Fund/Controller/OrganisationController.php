<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Fund\Entity\Organisation;

class OrganisationController extends AbstractRestfulController
{
    protected $organisationService;

    public function getList()
    {
      $service     = $this->getOrganisationService();
      $organisations = $service->getAllOrganisations();
      return new ViewModel(
          array(
            'organisations' => $organisations
          )
      );
    }

    /*
    * Get the individual organisation page.
    *
    */
    public function get($uri)
    {
      $service     = $this->getOrganisationService();
      $organisation = $service->getOrganisationByUrl($uri);

      return new ViewModel(
          array(
            'organisation' => $organisation
          )
      );
    }

    public function getOrganisationService()
    {
        if (!$this->organisationService) {
            $this->organisationService = $this->getServiceLocator()->get('OrganisationService');
        }
        
        return $this->organisationService;
    }
}
