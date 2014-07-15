<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class FundController extends AbstractRestfulController
{
    public function getList()
    {
        $objectManager = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $view = new ViewModel();

        $repository = $objectManager->getRepository('Fund\Entity\Fund');
        $adapter = new DoctrineAdapter(new ORMPaginator($repository->createQueryBuilder('fund')));

        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        
        // set the number of items per page to 10
        $paginator->setItemCountPerPage(10);
        $view->setVariable('funds', $paginator);

        return $view;
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
