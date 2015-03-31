<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Paginator\Paginator;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;


class FundController extends AbstractRestfulController
{
    protected $fundService;

    public function getList()
    {
        $container      = new Container('fund');
        $service        = $this->getFundService();
        $params         = $this->params();

        $parameters = array(
          'sort'            => $params->fromQuery('sort', 'name'),
          'order'           => $params->fromQuery('order', 'ASC'),
          'page'     => $params->fromQuery('page', 1),
          //Filter fundcompany
          'company'         => $params->fromQuery('company', array()),
          //Filter textsearch
          'q'               => $params->fromQuery('q', ""),
          //Filter category
          'fondoutcategory' => $params->fromQuery('fondoutcategory', array())
        );

        $sustainability = $container->sustainability;
        $funds = $service->findFunds($parameters, $sustainability);

        //Get averages
        $measuredAverages = $service->findMeasuredAverages($funds);

        //Paginate
        $fundsPaginator = new Paginator(new CollectionAdapter($funds));
        $fundsPaginator->setCurrentPageNumber((int)$parameters['page']);
        $fundsPaginator->setItemCountPerPage(50);
        $fundsPaginator->setPageRange(10);

        $form = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\FundFilterForm');

        $form->setData($parameters);

        $container = new Container('fund');

        $sform = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\SustainabilityForm');

        $value = isset($container->sustainability) ? $container->sustainability : true;
        $sform->get('sustainability')->setValue($value);

        return new ViewModel(
            array(
                'query'   => $parameters,
                'funds'   => $fundsPaginator,
                'form'    => $form,
                'sform'   => $sform,
                'measuredAverages' => $measuredAverages,
            )
        );
    }

    /*
    * Get the individual fund page.
    *
    */
    public function get($url)
    {

        $container = new Container('fund');
        $service = $this->getFundService();
        $parameters = $this->params();
        $sustainability = $container->sustainability;

        //get fund by url to get id. ugly but works.
        $id = $service->getFundByUrl($url)->id;
        $fund = $service->getFund($id, $sustainability);
        $sustainabilityNames = $service->getSustainabilityCategories($sustainability);
        $banks = $service->getBanks($fund);
        $sharesCount = $service->getCountShares($fund);

        // Category
        $categoryFunds = $service->findSameCategoryFunds($fund);
        $categoryAverages = $service->findMeasuredAverages($categoryFunds);

        // Fund Company funds
        $fundCompanyFunds = $service->findSameFundCompanyFunds($fund);

        // All funds averages
        $allFundsAverages = $service->findAveragesAllFunds();

        // Fund held companies in measured categories w/ %
        $controCompanies = $service->findControversialCompanies($fund);


        return new ViewModel(
            array(
                'fund'          => $fund, // current fund
                'categoryFunds' => $categoryFunds, // funds same category
                'fCompanyFunds' => $fundCompanyFunds, // same fcompany
                'banks'         => $banks,   // where to buy the fund
                'sharesCount'   => $sharesCount, // fund share count
                'categoryAvg'   => $categoryAverages,
                'allFundsAvg'   => $allFundsAverages,
                'companies'     => $controCompanies,
            )
        );
    }

    public function getFundService()
    {
        if (!$this->fundService) {
            $this->fundService = $this->getServiceLocator()->get('FundService');
        }

        return $this->fundService;
    }

    public function changeCategoriesAction()
    {
        $container = new Container('fund');
        $container->sustainability = $this->params()->fromPost('sustainability', array());

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        if ($redirect) {
            return $this->redirect()->toRoute($redirect);
        }

        $url = $this->getRequest()->getHeader('Referer')->getUri();
        $this->redirect()->toUrl($url);
    }
}
