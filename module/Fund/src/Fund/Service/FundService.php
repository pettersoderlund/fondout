<?php

namespace Fund\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;

use Fund\Entity\Fund;

/**
* FundService
*/
class FundService
{
    protected $entityManager;

    /**
     * Get fund by url relative to /funds
     *
     * @param  string $url
     * @return \Fund\Entity\Fund
     */
    public function getFundByUrl($url)
    {
        // get fund from repository
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $fund = $fundRepository->findOneBy(array('url' => $url));
        if (!$fund) {
            throw new \Exception();
        }
        return $fund;
    }

    /**
     * Get fundcompany by url relative to /fundcompany
     *
     * @param  string $url
     * @return \Fund\Entity\FundCompany
     */
    public function getFundCompanyByUrl($url)
    {
        // get fund from repository
        $repository = $this->getEntityManager()
          ->getRepository('Fund\Entity\FundCompany');
        $fundCompany = $repository->findOneBy(array('url' => $url));
        if (!$fundCompany) {
            throw new \Exception();
        }
        return $fundCompany;
    }

    /**
     * Get organisation by url relative to /organisation
     *
     * @param  string $url
     * @return \Fund\Entity\Organisation
     */
    public function getOrganisationByUrl($url)
    {
        // get fund from repository
        $repository = $this->getEntityManager()
          ->getRepository('Fund\Entity\Organisation');
        $organisation = $repository->findOneBy(array('url' => $url));
        if (!$organisation) {
            throw new \Exception();
        }
        return $organisation;
    }

    public function getFundById($id)
    {
        // get fund from repository
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $fund = $fundRepository->find($id);
        if (!$fund) {
            throw new \Exception();
        }
        return $fund;
    }

    public function getFund($id)
    {
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        //return current($fundRepository->mapControversialMarketValues($this->getFundById($id)));
        return $fundRepository->findOneById($id);
    }

    /**
     * Search
     *
     * NOTE: currently only search by name is available
     * TODO: migrate to ElasticSearch
     *
     * @param  string[] $params
     * @return \Fund\Entity\Fund[]
     */
    public function search($params)
    {
        if (!isset($params['q'])) {
            throw new \InvalidArgumentException();
        }
        // get funds from repository
        $fundRepository = $this->getEntityManager()
            ->getRepository('Fund\Entity\Fund');
        return $fundRepository->searchByName($params['q']);
    }

    /**
     * Get three lists of companies of the funds holdings of
     * companies with weapons, fossils and alcohol, tobacco, gambling w/
     * a percentage of how large the holding is.
     *
     * @param Fund $fund
     * @return
     */
    public function findControversialCompanies(Fund $fund)
    {
        $fundRepository = $this->getEntityManager()
            ->getRepository('Fund\Entity\Fund');

        $acr = $this->getEntityManager()
                ->getRepository('Fund\Entity\AccusationCategory');

        // $allControversialCompanies is for counting categories

        if ($fund->weaponCompanies > 0) {
          $weaponCompanies = $fundRepository
            ->findControversialCompanies($fund, $acr
              ->findOneByName('Förbjudna vapen'));
        } else {
          $weaponCompanies = array();
        }
        if ($fund->fossilCompanies > 0) {
          $fossilCompanies = $fundRepository
            ->findControversialCompanies($fund, $acr
              ->findOneByName('Fossila bränslen'));
        } else {
          $fossilCompanies = array();
        }
        if ($fund->alcoholCompanies > 0) {
          $alcoholCompanies = $fundRepository
            ->findControversialCompanies($fund, $acr
              ->findOneByName('Alkohol'));
        } else {
          $alcoholCompanies = array();
        }
        if ($fund->tobaccoCompanies > 0) {
          $tobaccoCompanies = $fundRepository
            ->findControversialCompanies($fund, $acr
              ->findOneByName('Tobak'));
        } else {
          $tobaccoCompanies = array();
        }
        if ($fund->gamblingCompanies > 0) {
          $gamblingCompanies = $fundRepository
            ->findControversialCompanies($fund, $acr
              ->findOneByName('Spel'));
        } else {
          $gamblingCompanies = array();
        }

        return array(
            "weapon"   => $weaponCompanies,
            "fossil"   => $fossilCompanies,
            "alcohol"  => $alcoholCompanies,
            "tobacco"  => $tobaccoCompanies,
            "gambling" => $gamblingCompanies
          );
    }

    public function getSustainabilityCategories($sustainability = array())
    {
        return $this->getEntityManager()
            ->getRepository('Fund\Entity\AccusationCategory')
            ->findBy(array('id' => $sustainability));
    }

    /**
     * Get a list of funds with the specified order and filters.
     *
     * @param string[] $parameters
     * @return Fund[]
     */
    public function findFunds($params, $sustainability = array())
    {
        $sort            = $params['sort'];
        $order           = $params['order'];
        $currentPage     = $params['page'];
        //Filter fundcompany
        $company         = $params['company'];
        //Filter specific funds
        $fund            = $params['fund'];
        //Filter textsearch
        $q               = $params['q'];
        //Filter category
        $fondoutcategory = $params['fondoutcategory'];


        $sortOrder = array();
        switch ($sort) {
            case 'name':
                # Sorting az on critiria puts AMF before Ad
                # Sorting depending on uppercase letters,
                # Fixed by default sorting in the original query in fundrepo

                if ($order == 'DESC') {
                  $sortOrder['name'] = $order;
                }
                break;
            case 'weapon':
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['alcoholCompanies'] = $order;
                $sortOrder['gamblingCompanies'] = $order;
                $sortOrder['tobaccoCompanies'] = $order;
                break;
            case 'fossil':
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['alcoholCompanies'] = $order;
                $sortOrder['gamblingCompanies'] = $order;
                $sortOrder['tobaccoCompanies'] = $order;
                break;
            case 'alcohol':
                $sortOrder['alcoholCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['gamblingCompanies'] = $order;
                $sortOrder['tobaccoCompanies'] = $order;
                break;
            case 'tobacco':
                $sortOrder['tobaccoCompanies'] = $order;
                $sortOrder['alcoholCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['gamblingCompanies'] = $order;
                break;
            case 'gambling':
                $sortOrder['gamblingCompanies'] = $order;
                $sortOrder['tobaccoCompanies'] = $order;
                $sortOrder['alcoholCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                break;
            case 'nav1year':
                $sortOrder['nav1year'] = $order;
                break;
            case 'nav3year':
                $sortOrder['nav3year'] = $order;
                break;
            case 'nav5year':
                $sortOrder['nav5year'] = $order;
                break;
            case 'shp':
                $sortOrder['shp'] = $order;
                break;
        }


        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy($sortOrder);

        if (count($company) > 0) {
            $criteria->andWhere(Criteria::expr()->in('companyId', $company));
        }

        if (count($fund) > 0) {
            $criteria->andWhere(Criteria::expr()->in('id', $fund));
        }

        if (count($fondoutcategory) > 0) {
            $criteria->andWhere(Criteria::expr()->in('fondoutcategoryId', $fondoutcategory));
        }

        $q = trim($q);
        if (strlen($q) > 0) {
            $criteria->andWhere(Criteria::expr()->orX(
                Criteria::expr()->contains('name', $q),
                Criteria::expr()->contains('name', strtoupper($q)),
                Criteria::expr()->contains('url', strtolower($q))
            ));
        }

        $funds        = new ArrayCollection($repository->findAllFunds($sustainability));
        $orderedFunds = $funds->matching($criteria);

        return $orderedFunds;
    }

    /**
     * Get a list of funds of the same fundcategory as the given fund
     *
     * @param \Fund\Entity\Fund
     * @return Fund collection
     */
    public function findSameCategoryFunds($fund)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(
            array('weaponCompanies' => 'ASC', 'fossilCompanies' => 'ASC',
              'alcoholCompanies' => 'ASC', 'tobaccoCompanies' => 'ASC',
              'gamblingCompanies' => 'ASC')
        );
        $criteria->andWhere(Criteria::expr()->eq('fondoutcategoryId', $fund->fondoutcategory->id));
        $criteria->andWhere(Criteria::expr()->neq('id', $fund->id));
        //$criteria->setMaxResults(5);
        $funds        = new ArrayCollection($repository->findAllFunds());
        $orderedfunds = $funds->matching($criteria);

        return $orderedfunds;
    }

    /**
     * Get a list of funds of the same fund company as the given fund
     * w/out the fund given.
     *
     * @param \Fund\Entity\Fund
     * @return Fund collection
     */
    public function findSameFundCompanyFunds($fund)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(
            array('weaponCompanies' => 'ASC', 'fossilCompanies' => 'ASC',
              'alcoholCompanies' => 'ASC', 'tobaccoCompanies' => 'ASC',
              'gamblingCompanies' => 'ASC')
        );
        $criteria->andWhere(Criteria::expr()->eq('fundCompanyId', $fund->company->id));
        $criteria->andWhere(Criteria::expr()->neq('id', $fund->id));
        //$criteria->setMaxResults(5);
        $funds        = new ArrayCollection($repository->findAllFunds());
        $orderedfunds = $funds->matching($criteria);

        return $orderedfunds;
    }

    /**
     * Get a list of funds of the same fund company
     *
     * @param \Fund\Entity\FundCompany
     * @return Fund collection
     */
    public function findFundCompanyFunds($fundCompany)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        /*$criteria = Criteria::create()->orderBy(
            array('weaponCompanies' => 'ASC', 'fossilCompanies' => 'ASC',
              'alcoholCompanies' => 'ASC', 'tobaccoCompanies' => 'ASC',
              'gamblingCompanies' => 'ASC')
        );*/
        $criteria = Criteria::create()->orderBy(
            array('name' => 'ASC')
        );
        $criteria->andWhere(Criteria::expr()->eq('fundCompanyId', $fundCompany->id));
        $funds        = new ArrayCollection($repository->findAllFunds());
        $orderedfunds = $funds->matching($criteria);

        return $orderedfunds;
    }

    /**
    * Get measure averages for all funds.
    * @param
    * @return
    */
    public function findAveragesAllFunds() {
      $repository =
        $this->getEntityManager()->getRepository('Fund\Entity\Fund');
      $avgAllFunds = $repository->findOneByName('allfunds');
      return $avgAllFunds;
    }

    public function findCategoryAverages($fondoutCategory) {
      $repository =
        $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $repository->findOneByName($fondoutCategory->title);
    }

    /**
    * Get measure averages from given funds
    * Give funds
    * get averages for weapon, fossil and alcohol, tobacco, gambling
    */
    public function findMeasuredAverages($funds, $avgFund) {
      $weapon   = 0;
      $fossil   = 0;
      $alcohol  = 0;
      $tobacco  = 0;
      $gambling = 0;

      foreach ($funds as $fund) {
        $weapon   += $fund->getWeaponCompanies();
        $fossil   += $fund->getFossilCompanies();
        $alcohol  += $fund->getAlcoholCompanies();
        $tobacco  += $fund->getTobaccoCompanies();
        $gambling += $fund->getGamblingCompanies();
      }

      //To remove division by zero risk
      //set all avg to 0 if 0 funds given.
      if(sizeof($funds) == 0) {
        $fundCount = 1;
      } else {
        $fundCount = sizeof($funds);
      }

      $avgWeapon   = (int)($weapon/$fundCount);
      $avgFossil   = (int)($fossil/$fundCount);
      $avgAlcohol  = (int)($alcohol/$fundCount);
      $avgTobacco  = (int)($tobacco/$fundCount);
      $avgGambling = (int)($gambling/$fundCount);

      $avgFund->setWeaponCompanies($avgWeapon);
      $avgFund->setFossilCompanies($avgFossil);
      $avgFund->setAlcoholCompanies($avgAlcohol);
      $avgFund->setTobaccoCompanies($avgTobacco);
      $avgFund->setGamblingCompanies($avgGambling);

      return $avgFund;
    }

    /**
    * Retreive an array with accusation categories as value
    * with the english shortname as key.
    */

    public function findAccusationCategories() {
      $accCat = array();
      $repository = $this->getEntityManager()
        ->getRepository('Fund\Entity\AccusationCategory');
      $accCat['fossil'] = $repository->findOneByName('Fossila bränslen');
      $accCat['weapon'] = $repository->findOneByName('Förbjudna vapen');
      $accCat['alcohol'] = $repository->findOneByName('Alkohol');
      $accCat['tobacco'] = $repository->findOneByName('Tobak');
      $accCat['gambling'] = $repository->findOneByName('Spel');

      return $accCat;
    }


    /** TODO:
    * Include fund company pages
    */
    public function createSitemap() {
      $em = $this->getEntityManager();

      $sitemap = "";
      $sitemap .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
      $sitemap .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

      $sitemap .= "  <url>\n";
      $sitemap .= "    <loc>http://www.sparahallbart.se/funds</loc>\n";
      $sitemap .= "  </url>\n";

      $sitemap .= "  <url>\n";
      $sitemap .= "    <loc>http://www.sparahallbart.se/qa</loc>\n";
      $sitemap .= "  </url>\n";

      $fr = $em->getRepository('Fund\Entity\Fund');
      $funds = $fr->findAllFunds();

      // Reset all values.
      foreach ($funds as $fund) {
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>http://www.sparahallbart.se/funds/$fund->url</loc>\n";
        $sitemap .= "  </url>\n";
      }

      // add all fundcompany pages.

      $sitemap .= "</urlset>\n";
      return $sitemap;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
