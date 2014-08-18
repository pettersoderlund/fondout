<?php

namespace Fund\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
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
        $fund = $fundRepository->findOneBy(['url' => $url]);
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
     * Get a list of all controversial companies filtered to be visible on the
     * fund page connected to the fund in a paginator as well as a count for
     * how many companies per category.
     *
     * TODO: Filter controversial companies from parameters
     * @param Fund $fund, string[] $parameters
     * @return Zend\Paginator\Paginator, controversialcategoriesCount
     */
    public function findControversialCompanies(Fund $fund, $parameters, $sustainability = array())
    {
        // Controversial companies listed on fundpage
        // Chosen through a form
        $category_visible    = $parameters->fromQuery('category_visible', array());
        $currentPage         = $parameters->fromQuery('page', 1);

        $fundRepository = $this->getEntityManager()
            ->getRepository('Fund\Entity\Fund');

        // $allControversialCompanies is for counting categories
        $allControversialCompanies =
         $fundRepository->findControversialCompanies($fund, $sustainability);

        // $controversialCompanies is for listing companies.
        if (count($category_visible) > 0) {
            $controversialCompanies =
             $fundRepository->findControversialCompanies($fund, $category_visible);
        } else {
            $controversialCompanies = $allControversialCompanies;
        }

        // Count the number of occurances for each category
        // $controversialCategoriesCount[categoryId] = array(categoryName, categoryCount)
        $controversialCategoriesCount = array();
        foreach ($allControversialCompanies as $company) {
            foreach ($company->accusations as $accusation) {
                $accusationId = $accusation->category->id;
                if (array_key_exists($accusationId, $controversialCategoriesCount)) {
                    $controversialCategoriesCount[$accusationId][1]++;
                } else {
                    $accusationName = $accusation->category->name;
                    $controversialCategoriesCount[$accusationId] = array($accusationName, 1);
                }
            }
        }

        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($controversialCompanies)
        );

        $paginator->setCurrentPageNumber((int)$currentPage);
        $paginator->setItemCountPerPage(10);

        return array($paginator, $controversialCategoriesCount);
    }

    public function findControversialValue(Fund $fund, $sustainability = array())
    {
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $fundRepository->findControversialValue($fund, $sustainability);
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
     * Get a list of funds, in a paginator with the specified order and filters.
     *
     * @param string[] $parameters
     * @return Zend\Paginator\Paginator
     */
    public function findFunds($params, $sustainability = array())
    {
        $sort        = $params->fromQuery('sort', 'name');
        $order       = $params->fromQuery('order', 'ASC');
        $currentPage = $params->fromQuery('page', 1);
        $company     = $params->fromQuery('company', array());
        $size        = $params->fromQuery('size', array());

        $fondoutcategory = $params->fromQuery('fondoutcategory', array());

        switch ($sort) {
            case 'company':
                $sort = 'companyName';
                break;
            case 'fondoutcategory':
                $sort = 'fondoutCategoryTitle';
                break;
            case 'size':
                $sort = 'totalMarketValue';
                break;
        }

        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(array($sort => $order));

        if (count($company) > 0) {
            $criteria->andWhere(Criteria::expr()->in('companyId', $company));
        }

        if (count($fondoutcategory) > 0) {
            $criteria->andWhere(Criteria::expr()->in('fondoutcategoryId', $fondoutcategory));
        }

        if (count($size) > 0) {
            $sizeCriteria = array();
            foreach ($size as $s) {
                switch ($s) {
                    case "small":
                        $sizeCriteria[] = Criteria::expr()->lte('totalMarketValue', 600000000);
                        break;
                    case "medium":
                        $sizeCriteria[] = Criteria::expr()->andx(
                            Criteria::expr()->gt('totalMarketValue', 600000000),
                            Criteria::expr()->lt('totalMarketValue', 2500000000)
                        );
                        break;
                    case "large":
                        $sizeCriteria[] = Criteria::expr()->gte('totalMarketValue', 2500000000);
                        break;
                    default:
                }
            }
            $criteria->andWhere(call_user_func_array(array(Criteria::expr(), "orx"), $sizeCriteria));
        }

        $funds        = new ArrayCollection($repository->findAllFunds($sustainability));
        $orderedfunds = $funds->matching($criteria);
        $paginator    = new Paginator(new CollectionAdapter($orderedfunds));

        $paginator->setCurrentPageNumber((int)$currentPage);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }

    /**
     * Get a list of funds, in a paginator with the specified order and filters.
     *
     * @param \Fund\Entity\Fund, string[] $sustainability
     * @return Fund collection
     */
    public function findSameCategoryFunds($fund, $sustainability = array())
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(array('sustainability' => 'DESC'));
        $criteria->andWhere(Criteria::expr()->eq('fondoutcategoryId', $fund->fondoutcategory->id));
        $criteria->andWhere(Criteria::expr()->neq('id', $fund->id));
        $criteria->setMaxResults(10);
        $funds        = new ArrayCollection($repository->findAllFunds($sustainability));
        $orderedfunds = $funds->matching($criteria);

        return $orderedfunds;
    }

    /**
    * Get the number of controversial shares for the given fund
    *
    * @param \Fund\Entity\Fund, string[] $sustainability
    * @return int numberOfControversialShares
    */
    public function getCountControverisalShares($fund, $sustainability = array())
    {
        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $repository->countControversialShares($fund, $sustainability);
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
}
