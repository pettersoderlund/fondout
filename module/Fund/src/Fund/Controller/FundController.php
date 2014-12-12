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
        $params         = $this->params();

        $parameters = array(
          'sort'            => $params->fromQuery('sort', 'sustainability'),
          'order'           => $params->fromQuery('order', 'DESC'),
          'page'     => $params->fromQuery('page', 1),
          //Filter fundcompany
          'company'         => $params->fromQuery('company', array()),
          //Filter fundsize
          'size'            => $params->fromQuery('size', array()),
          //Filter textsearch
          'q'               => $params->fromQuery('q', ""),
          //Filter category
          'fondoutcategory' => $params->fromQuery('fondoutcategory', array()),
          //Filter sustainability-score (1-10)
          'sustainabilityscore' => $params->fromQuery('sustainabilityscore', array())
        );

        $sustainability = $container->sustainability;
        $fundsPaginator = $service->findFunds($parameters, $sustainability);
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
                'query' => $parameters,
                'funds' => $fundsPaginator,
                'form' => $form,
                'sform' => $sform
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

        $container = new Container('fund');
        $service = $this->getFundService();
        $parameters = $this->params();
        $sustainability = $container->sustainability;
        $fund = $service->getFund($id, $sustainability);
        $funds = $service->findSameCategoryFunds($fund, $sustainability);
        $sustainabilityNames = $service->getSustainabilityCategories($sustainability);
        $banks = $service->getBanks($fund);

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
                'sform'                  => $sform // Sustainability cat. form
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
