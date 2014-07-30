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
     * Get a list of all controversial companies connected to the fund
     * in a paginator
     *
     * TODO: Filter controversial companies from parameters
     * @param Fund $fund, string[] $parameters
     * @return Zend\Paginator\Paginator
     */
    public function findControversialCompanies(Fund $fund, $parameters)
    {
        $category    = $parameters->fromQuery('category_visible', array());
        $currentPage = $parameters->fromQuery('page', 1);

        $fundRepository = $this->getEntityManager()
            ->getRepository('Fund\Entity\Fund');


        $controversialCompanies =
         $fundRepository->findControversialCompanies($fund, $category);
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($controversialCompanies);
        die;*/
        // Count the number of occurances for each category
        $controversialCategoriesCount = array();
        foreach ($controversialCompanies as $company) {
            foreach ($company->accusations as $accusation) {
                $accusationName = $accusation->category->name;
                if (array_key_exists($accusationName, $controversialCategoriesCount)) {
                    $controversialCategoriesCount[$accusationName]++;
                } else {
                    $controversialCategoriesCount[$accusationName] = 1;
                }

            }
        }
        //echo var_dump($controversialCategoriesCount);

        /*$adapter = new DoctrineAdapter(
            new ORMPaginator($fundRepository->findControversialCompanies($fund))
        );

        $paginator = new Paginator($adapter);*/

        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($controversialCompanies)
        );

        $paginator->setCurrentPageNumber((int)$currentPage);
        $paginator->setItemCountPerPage(10);

        return array($paginator, $controversialCategoriesCount);
    }

    public function findControversialValue(Fund $fund)
    {
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $fundRepository->findControversialValue($fund);
    }

    /**
     * Similar funds sorted by blacklisted shares ratio
     *
     * This method returns a list of the funds with the lowest marketvalue
     * of blacklisted funds from the same category of funds as the fund given.
     *
     * @param  \Fund\Entity\Fund $fund, string[] $categories, string[] $organizations
     * @return \Fund\Entity\Fund[]
     */
    public function getSimilarFunds(\Fund\Entity\Fund $fund, $categories, $organizations)
    {
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $fundRepository->findSimilarFunds($fund, $categories, $organizations);
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get a list of funds, in a paginator with the specified order and filters.
     *
     * @param string[] $parameters
     * @return Zend\Paginator\Paginator
     */
    public function findFunds($params)
    {
        $sort        = $params->fromQuery('sort', 'name');
        $order       = $params->fromQuery('order', 'ASC');
        $currentPage = $params->fromQuery('page', 1);
        $category    = $params->fromQuery('category', array());
        $company     = $params->fromQuery('company', array());

        $sort = ($sort == 'company') ? 'companyName' : $sort;

        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $criteria = Criteria::create()->orderBy(array($sort => $order));

        if (count($company) > 0) {
            $criteria->where(Criteria::expr()->in('companyId', $company));
        }

        $funds        = new ArrayCollection($repository->findAllFunds($category));
        $orderedfunds = $funds->matching($criteria);
        $paginator    = new Paginator(new CollectionAdapter($orderedfunds));

        $paginator->setCurrentPageNumber((int)$currentPage);
        $paginator->setItemCountPerPage(10);

        return $paginator;
    }
}
