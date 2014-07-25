<?php

namespace Fund\Service;

use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

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
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $fundRepository->searchByName($params['q']);
    }


    public function findControversialCompanies(Fund $fund)
    {
        $fundRepository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        return $fundRepository->findControversialCompanies($fund);
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
     * @param string[] $params
     * @return Zend\Paginator\Paginator
     */
    public function findFunds($criteria)
    {
        // Check if order by is set, defaults to column fund name
        $sort = (isset($criteria['sort'])) ? $criteria['sort'] : 'name';

        // Check if order is set, defaults to ascending
        $order = (isset($criteria['order'])) ? $criteria['order'] : 'ASC';


        $repository = $this->getEntityManager()->getRepository('Fund\Entity\Fund');
        $query = $repository->createQueryBuilder('fund')->select('fund, fi')->join('fund.fundInstances', 'fi')->orderBy('fund.' . $sort, $order);
        $paginator = new Paginator(new DoctrineAdapter(new ORMPaginator($query)));

        // Check if page is set, defaults to page 1
        $currentPage = (isset($criteria['page'])) ? $criteria['page'] : 1;

        $paginator->setCurrentPageNumber((int)$currentPage);
        $paginator->setItemCountPerPage(10);

        return $repository->mapControversialMarketValues($paginator, $criteria);
    }
}
