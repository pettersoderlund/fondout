<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;

class FundController extends AbstractRestfulController
{
    protected $fundService;

    public function getList()
    {
        $service = $this->getFundService();
        $parameters = $this->params();
        $fundsPaginator = $service->findFunds($parameters);
        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\FundFilterForm');
        $form->setData($parameters->fromQuery());

        return new ViewModel(
            array(
              'funds' => $fundsPaginator,
              'query' => $parameters->fromQuery(),
              'form' => $form
            )
        );
    }

    public function get($id)
    {
        $service = $this->getFundService();
        $parameters = $this->params()->fromQuery();
        $fund = $service->getFundById($id);
        $controversialCompaniesPaginator = $service->findControversialCompanies(
            $fund,
            $parameters
        );
        $controversialValue = $service->findControversialValue($fund);

        return new ViewModel(
            array(
                'fund' => $fund,
                'controversialCompanies' => $controversialCompaniesPaginator,
                'controversialValue' => $controversialValue,
                'queryParameters' => $parameters
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
