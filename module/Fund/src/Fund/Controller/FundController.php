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

    /*
    * Get the individual fund page.
    * TODO: Count controversial companies/securities and total number of securit
    *
    */
    public function get($id)
    {
        $service = $this->getFundService();
        $parameters = $this->params();
        $fund = $service->getFundById($id);
        list ($controversialCompaniesPaginator, $cCategoriesCount)
            = $service->findControversialCompanies(
                $fund,
                $parameters
            );
        $controversialValue = $service->findControversialValue($fund);

        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\FundPageFilterForm');
        $form->setData($parameters->fromQuery());

        return new ViewModel(
            array(
                'fund' => $fund,
                'controversialCompanies' => $controversialCompaniesPaginator,
                'controversialValue' => $controversialValue,
                'cCategoriesCount' => $cCategoriesCount,
                'queryParameters' => $parameters->fromQuery(),
                'form' => $form
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
