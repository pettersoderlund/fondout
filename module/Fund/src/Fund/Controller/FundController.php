<?php
namespace Fund\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Paginator\Paginator;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Fund\Entity\Fund;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;

class FundController extends AbstractRestfulController
{
    protected $fundService;

    public function getList()
    {
        $container             = new Container('fund');
        $service               = $this->getFundService();
        $params                = $this->params();
        $default_fund_category = 6; // 6 is global funds category

        $parameters = array(
          'sort'            => $params->fromQuery('sort', 'shp'),
          'order'           => $params->fromQuery('order', 'ASC'),
          'page'     => $params->fromQuery('page', 1),
          //Filter fundcompany
          'company'         => $params->fromQuery('company', array()),
          //Filter fund
          'fund'         => $params->fromQuery('fund', array()),
          //Filter textsearch
          'q'               => $params->fromQuery('q', ""),
          //Filter category
          'fondoutcategory' => $params->fromQuery(
            'fondoutcategory', array($default_fund_category)
          )
        );

        $sustainability = $container->sustainability;
        $funds = $service->findFunds($parameters, $sustainability);

        //Get averages

        $avgFund = $service->findMeasuredAverages($funds, new Fund());

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

        //$fundCategories = array('5'=>'Sverige', '6' => 'Global');
        $fundCategories = $service->findFundCategories();

        return new ViewModel(
            array(
                'query'   => $parameters,
                'funds'   => $fundsPaginator,
                'form'    => $form,
                'sform'   => $sform,
                'avgfund' => $avgFund,
                'fund_categories' => $fundCategories
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
        //$sustainabilityNames = $service->getSustainabilityCategories($sustainability);

        $accusationCategories = $service->findAccusationCategories();

        // Category
        $categoryFunds = $service->findSameCategoryFunds($fund);
        $avgCatFund = $service->findCategoryAverages($fund->fondoutCategory);

        // Fund Company funds
        // ONly if fund is premium
        if ($fund->fundCompany->premium) {
          $fundCompanyFunds = $service->findFundCompanyFunds($fund->fundCompany);
        } else {
          $fundCompanyFunds = null;
        }

        // All funds averages
        $avgAllFund = $service->findAveragesAllFunds(new Fund());

        // Fund held companies in measured categories w/ %
        $controCompanies = $service->findControversialCompanies($fund);

        $backuri = $this->getBackLink();

        return new ViewModel(
            array(
                'fund'          => $fund, // current fund
                'categoryFunds' => $categoryFunds, // funds same category
                'fCompanyFunds' => $fundCompanyFunds, // same fcompany
                //'sharesCount'   => $sharesCount, // fund share count
                // Avg company count all same category
                'avgcategory'   => $avgCatFund,
                // Avg company count all funds
                'avgallfunds'   => $avgAllFund,
                // Companies from accusation categories
                'companies'     => $controCompanies,
                // Backlink to fundsearch uri
                'backuri'       => $backuri,
                //accusation category array
                'accCat'        => $accusationCategories
            )
        );
    }

    public function getFundCompanyAction()
    {
      $service     = $this->getFundService();
      $uri         = $this->params()->fromRoute('name');
      $fundCompany = $service->getFundCompanyByUrl($uri);
      $funds       = $service->findFundCompanyFunds($fundCompany);
      $backuri     = $this->getBackLink();

      return new ViewModel(
          array(
            'fundCompany' => $fundCompany,
            'funds'       => $funds,
            'backuri'     => $backuri
          )
      );
    }

    public function getOrganisationAction()
    {
      $service     = $this->getFundService();
      $uri         = $this->params()->fromRoute('url');
      $backuri     = $this->getBackLink();
      $organisation = $service->getOrganisationByUrl($uri);

      //$funds       = $service->findFundCompanyFunds($fundCompany);


      return new ViewModel(
          array(
            'organisation' => $organisation,
            'backuri'      => $backuri
          )
      );
    }


    public function getAPAction()
    {
      $service     = $this->getFundService();

      $funds = new ArrayCollection();
      $funds->add($service->getFundByUrl('ap1'));
      $funds->add($service->getFundByUrl('ap2'));
      $funds->add($service->getFundByUrl('ap3'));
      $funds->add($service->getFundByUrl('ap4'));
      $funds->add($service->getFundByUrl('ap7-aktiefond'));

      $funds = $funds->matching(Criteria::create()->orderBy(array('name' => 'asc')));
      $backuri     = $this->getBackLink();

      $view =  new ViewModel(
          array(
            'funds'       => $funds,
            'backuri'     => $backuri
          )
      );
      //$view->setTemplate('fund/fund/get-fund-company');
      return $view;
    }

    public function getQAAction()
    {
      $service     = $this->getFundService();
      $accusationCategories = $service->findAccusationCategories();

      return new ViewModel(
          array(
            'accCategories' => $accusationCategories
          )
      );
    }

    public function getProductsAction()
    {
      return new ViewModel();
    }

    public function getPressAction()
    {
      $result = new ViewModel();
      $result->setTerminal(true);
      return $result;
    }

		public function getPensionAction()
		{
			return new ViewModel();
		}

		public function getSchoolAction()
		{
			return new ViewModel();
		}

    public function getGoogleAction()
    {
      $result = new ViewModel();
      $result->setTerminal(true);
      return $result;
    }

    public function getSitemapAction()
    {
      $service = $this->getFundService();
      $sitemap = $service->createSitemap();
      echo $sitemap;

			return $this->response;
    }

    public function getFundService()
    {
        if (!$this->fundService) {
            $this->fundService = $this->getServiceLocator()->get('FundService');
        }

        return $this->fundService;
    }

    public function getBackLink() {
      // To create a back link
      $referer = $this->getRequest()->getHeader('Referer');
      $backuri = "";
      if ($referer) {
        $backuri = $referer->getUri();
      }

      // is it a search q backlink?
      if(!strpos($backuri, "funds?")) {
        $backuri = "/funds";
      }
      return $backuri;
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
