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
        echo $url;
        $fund = $fundRepository->findOneBy(array('url' => $url));
        if (!$fund) {
            throw new \Exception();
        }
        return $fund;
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
        return current(
            $fundRepository->mapControversialMarketValues($this->getFundById($id))
        );
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
     * companies with weapons, fossils and altogas w/
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
        $weaponCompanies = $fundRepository
          ->findControversialCompanies($fund, $acr
            ->findOneByName('Kontroversiella vapen'));
        $fossilCompanies = $fundRepository
          ->findControversialCompanies($fund, $acr
            ->findOneByName('Fossila bränslen'));
        $altogaCompanies = $fundRepository
          ->findControversialCompanies($fund, $acr
            ->findOneByName('Alkohol, tobak, spel'));

        return array(
            "weapon" => $weaponCompanies,
            "fossil" => $fossilCompanies,
            "altoga" => $altogaCompanies
          );
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
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
        //Filter textsearch
        $q               = $params['q'];
        //Filter category
        $fondoutcategory = $params['fondoutcategory'];


        $sortOrder = array();
        switch ($sort) {
            case 'name':
                $sortOrder['name'] = $order;
                break;
            case 'weapon':
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['alToGaCompanies'] = $order;
                break;
            case 'fossil':
                $sortOrder['fossilCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['alToGaCompanies'] = $order;
                break;
            case 'altoga':
                $sortOrder['alToGaCompanies'] = $order;
                $sortOrder['weaponCompanies'] = $order;
                $sortOrder['fossilCompanies'] = $order;
                break;
        }


        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy($sortOrder);

        if (count($company) > 0) {
            $criteria->andWhere(Criteria::expr()->in('companyId', $company));
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
     * Get a list of funds, in a paginator with the specified order and filters.
     *
     * @param \Fund\Entity\Fund
     * @return Fund collection
     */
    public function findSameCategoryFunds($fund)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(
            array('weaponCompanies' => 'ASC', 'fossilCompanies' => 'ASC',  'alToGaCompanies' => 'ASC')
        );
        $criteria->andWhere(Criteria::expr()->eq('fondoutcategoryId', $fund->fondoutcategory->id));
        $criteria->andWhere(Criteria::expr()->neq('id', $fund->id));
        //$criteria->setMaxResults(5);
        $funds        = new ArrayCollection($repository->findAllFunds());
        $orderedfunds = $funds->matching($criteria);

        return $orderedfunds;
    }

    /**
    * Get the total count of shares w/ marketvalue>0 for the given fund
    *
    * @param \Fund\Entity\Fund
    * @return int numberOfShares
    */
    public function getCountShares($fund)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $repository->countShares($fund);
    }

    /**
    * Get a array of banks that offer the fund with a link (URL)
    *
    * @param \Fund\Entity\Fund
    * @return mixed[] Banks
    */
    public function getBanks($fund)
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $repository->findBanks($fund);
    }

    /**
    * Get measure averages for all funds.
    * @param
    * @return
    */
    public function findAveragesAllFunds() {
      $repository =
        $this->getEntityManager()->getRepository('Fund\Entity\Fund');
      $funds = new ArrayCollection($repository->findAllFunds());
      return $this->findMeasuredAverages($funds);
    }

    /**
    * Get measure averages from given funds
    * Give funds
    * get averages for weapon, fossil and altoga
    */
    public function findMeasuredAverages($funds) {
      sizeof($funds);
      $weapon = 0;
      $fossil = 0;
      $alToGa = 0;

      foreach ($funds as $fund) {
        $weapon += $fund->getMeasureScore('weapon');
        $fossil += $fund->getMeasureScore('fossil');
        $alToGa += $fund->getMeasureScore('altoga');
      }

      $avgWeapon = (int)($weapon/sizeof($funds));
      $avgFossil = (int)($fossil/sizeof($funds));
      $avgAlToGa = (int)($alToGa/sizeof($funds));

      return array(
        "weapon" => $avgWeapon,
        "fossil" => $avgFossil,
        "altoga" => $avgAlToGa
      );
    }


}
