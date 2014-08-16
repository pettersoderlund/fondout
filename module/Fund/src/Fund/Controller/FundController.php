<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class FundController extends AbstractRestfulController
{
    protected $fundService;

    public function getList()
    {
        $container      = new Container('fund');
        $service        = $this->getFundService();
        $parameters     = $this->params();
        $sustainability = $container->sustainability;
        $fundsPaginator = $service->findFunds($parameters, $sustainability);
        $names = $service->getSustainabilityCategories($sustainability);


        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\FundFilterForm');

        $form->setData($parameters->fromQuery());


        return new ViewModel(
            array(
                'sustainability' => $names,
                'query' => $parameters->fromQuery(),
                'funds' => $fundsPaginator,
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
        $container      = new Container('fund');
        $service = $this->getFundService();
        $parameters = $this->params();
        $sustainability = $container->sustainability;
        $fund = $service->getFundById($id);
        $funds = $service->findSameCategoryFunds($fund, $sustainability);

        list ($controversialCompaniesPaginator, $cCategoriesCount)
            = $service->findControversialCompanies(
                $fund,
                $parameters,
                $sustainability
            );
        $controversialValue = $service->findControversialValue($fund);

        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\FundPageFilterForm');

        $form->setCategories($cCategoriesCount);
        $form->setData($parameters->fromQuery());

        return new ViewModel(
            array(
                'fund'                   => $fund,
                'funds'                  => $funds,
                'controversialCompanies' => $controversialCompaniesPaginator,
                'controversialValue'     => $controversialValue,
                'cCategoriesCount'       => $cCategoriesCount,
                'queryParameters'        => $parameters->fromQuery(),
                'form'                   => $form
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
