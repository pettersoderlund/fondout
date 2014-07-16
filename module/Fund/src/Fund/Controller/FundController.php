<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class FundController extends AbstractRestfulController
{
    protected $fundService;

    public function getList()
    {
        $order_by = $this->params()->fromQuery('order_by', 'name');
        $order = $this->params()->fromQuery('order', 'ASC');

        $objectManager = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $repository = $objectManager->getRepository('Fund\Entity\Fund');
        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $repository->createQueryBuilder('fund')
                  ->orderBy('fund.' . $order_by, $order)
            )
        );

        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));

        // set the number of items per page to 10
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
                              'funds' => $paginator,
                              'queryParameters' => array(
                                  'order_by' => $order_by,
                                  'order' => $order,
                            )));
    }

    public function get($id)
    {
        $service = $this->getFundService();
        $fund = $service->getFundById($id);
        $controversialCompanies = $service->findControversialCompanies($fund);
        $controversialValue = $service->findControversialValue($fund);

        return new ViewModel(
            array(
                'fund' => $fund,
                'controversialCompanies' => $controversialCompanies,
                'controversialValue' => $controversialValue
            )
        );
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

    public function getFundService()
    {
        if (!$this->fundService) {
            $this->fundService = $this->getServiceLocator()->get('FundService');
        }

        return $this->fundService;
    }
}
