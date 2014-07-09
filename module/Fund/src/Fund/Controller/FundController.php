<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;

class FundController extends AbstractRestfulController
{
    public function getList()
    {
        $objectManager = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        return new ViewModel(
            array(
             'funds' => $objectManager->getRepository('Fund\Entity\Fund')->findAll()
            )
        );
    }

    public function get($id)
    {
        # code...
    }

    public function create($data)
    {
        # code...
    }

    public function update($id, $data)
    {
        # code...
    }

    public function delete($id)
    {
        # code...
    }
}
