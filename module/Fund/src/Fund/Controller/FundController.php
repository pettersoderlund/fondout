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

        $names = $service->getSustainabilityCategories($sustainability);


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
                'sustainability' => $names,
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
        $funds = $service->findSameCategoryFunds($fund, $sustainability);
        $sustainabilityNames = $service->getSustainabilityCategories($sustainability);
        $banks = $service->getBanks($fund);

        // Get average co2 from the category and co2Coverage category.
        /*
         * Would it be resonable to set the entity (fund->fondoutcategory)
         * in this stage? for the values of co2?
         */
        $category_co2 = $service->getAverageCo2Category($fund);
        $categoryCo2 = $category_co2[0][1];
        $categoryCo2Coverage = $category_co2[0][2];

        list ($controversialCompaniesPaginator, $cCategoriesCount)
            = $service->findControversialCompanies(
                $fund,
                $parameters,
                $sustainability
            );

        $cSharesCount = $service->getCountControverisalShares($fund, $sustainability);
        $sharesCount = $service->getCountShares($fund);

        $sform = $this->getServiceLocator()
            ->get('FormElementManager')
            ->get('\Fund\Form\SustainabilityForm');

        $value = isset($container->sustainability) ? $container->sustainability : true;
        $sform->get('sustainability')->setValue($value);

        return new ViewModel(
            array(
                'fund'                   => $fund, // the fund of the fund page
                'funds'                  => $funds, // funds in the same category
                'banks'                  => $banks, // where to buy the fund
                'sustainability'         => $sustainabilityNames, // Breached uniq. sust. categories
                'controversialCompanies' => $controversialCompaniesPaginator, // Companies breaching sust. cat. (not used atm. dec 14.)
                'cCategoriesCount'       => $cCategoriesCount, // number of company breaches of sust. cat. per category
                'cSharesCount'           => $cSharesCount, // number of share breaches of sust. cat. per category
                'sharesCount'            => $sharesCount, // total fund share count
                'query'                  => $parameters->fromQuery(), // ??? ... not used?
                //'form'                   => $form,
                'sform'                  => $sform, // Sustainability cat. form
                'categoryCo2'            => $categoryCo2,
                'categoryCo2Coverage'    => $categoryCo2Coverage
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
