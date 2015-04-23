<?php

namespace Fund\Repository;

use Doctrine\ORM\EntityRepository;

use Fund\Entity\Fund;

/**
* FundRepository
*/
class FundRepository extends EntityRepository
{
    /**
     * Search by name keyword using SQL's `LIKE` command
     *
     * TODO: third-party app for full-text search (ElasticSearch, Lucene) ?
     *
     * @param  string $keyword
     * @return \Fund\Entity\Fund
     */
    public function searchByName($keyword)
    {
        // Create querybuilder
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
            ->select('f.name', 'f.url')
            ->from('Fund\Entity\Fund', 'f')
            ->innerJoin('Fund\Entity\FundInstance', 'fi', 'WITH', 'f.id = fi.fund')
            ->where(
                $qb->expr()->like(
                    'f.name',
                    $qb->expr()->literal('%' . $keyword . '%')
                )
            )
           ->andwhere(
               $qb->expr()->gt(
                   'fi.date',
                   $qb->expr()->literal('2013-01-01')
               )
           )
           ->groupBy('f.name')
           ->getQuery()
           ->getResult();
    }

    /* This function is REALLY SLOW ~0.3 seconds. */
    public function findControversialCompanies(Fund $fund, $accCategory)
    {
        $subqb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('scc.id')
            ->from('Fund\Entity\ShareCompany', 'scc')
            ->join('scc.accusations', 'accusations')
            ->join('accusations.category', 'accusation_category')
            ->where('accusation_category.name = ?2')
            ->distinct();

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('sc.name as name,
              SUM(sh.marketValue)/fi.totalMarketValue as part')
              ->from('Fund\Entity\ShareCompany', 'sc')
              ->join('sc.shares', 's')
              ->join('s.shareholdings', 'sh')
              ->join('sh.fundInstance', 'fi')
              ->join('fi.fund', 'f')
              ->orderBy('sc.name', 'ASC')
              ->where('f.name = ?1')
              ->andWhere('sh.marketValue > 0')
              ->andWhere($qb->expr()->in('sc.id', $subqb->getDql()))
              ->groupBy('sc.name')
              ->orderBy('part', 'desc')
              ->setParameter(1, $fund->name)
              ->setParameter(2, $accCategory->name);

        return $qb
            ->getQuery()
            ->getResult();
    }


    public function countControversialShares(Fund $fund, $category = array())
    {
         $qb=  $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(DISTINCT s.isin)')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.accusations', 'b')
            ->join('b.category', 'c')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->where('f.name = ?1')
            ->andWhere('sh.marketValue > 0');

        if (count($category) > 0) {
            $qb->andWhere($qb->expr()->in('c.id', $category));
        }

        $qb->setParameter(1, $fund->name);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countShares(Fund $fund)
    {
         $qb=  $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(DISTINCT s.isin)')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->where('f.name = ?1')
            ->andWhere('sh.marketValue > 0');

        $qb->setParameter(1, $fund->name);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findControversialValue(Fund $fund, $category = array())
    {

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(sh.marketValue) as controversialValue')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.accusations', 'b')
            ->join('b.category', 'c')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->where('f.name = ?1');

        if (count($category) > 0) {
            $qb->andWhere($qb->expr()->in('c.id', $category));
        }

        return $qb
            ->setParameter(1, $fund->name)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function findAllFunds($category = array())
    {
        $funds = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('f, c, fi, fc')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('f.company', 'c')
            ->join('f.fondoutcategory', 'fc')
            ->orderBy('f.name', 'ASC')
            ->where('f.active = 1')
            ->getQuery()
            ->getResult();

        return $this->mapControversialMarketValues($funds, $category);
    }

    /**
    *
    * Second argument is an array of sustainability categories identified by id.
    */
    public function mapControversialMarketValues($funds, $category = array())
    {

        if ($funds instanceof Fund) {
            $funds = array($funds);
        }

        if (!is_array($funds)) {
            throw new \InvalidArgumentException();
        }

        $queryBuilder    = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder = clone $queryBuilder;
        $fundMap         = array();

        foreach ($funds as $fund) {
            $fund->setTotalMarketValue();
            $fundMap[$fund->id] = $fund;
        }

        // subquery: select all distinct accusations that match the category criteria
        // and the share company ID
        // NOTE 18/3 2015: Should be DISTINCT a.accusationCategory
        $subQueryBuilder->select('DISTINCT a.accusation')
            ->from('Fund\Entity\Accusation', 'a')
            ->join('a.category', 'c')
            ->where('a.shareCompany = sc.id');

        if (count($category) > 0) {
            $subQueryBuilder->andWhere($subQueryBuilder->expr()->in('c.id', $category));
        }

        // query: aggregate all market values for all controversial shareholdings per fund
        $queryBuilder->select('f.id, SUM(sh.marketValue) AS score')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->join('s.shareCompany', 'sc')
            ->where(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->exists($subQueryBuilder->getDql()),
                    $queryBuilder->expr()->in('f.id', array_keys($fundMap))
                )
            )->groupBy('f.id');

        // map the ControversialValue DTO to the related fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->calculateSustainability($cv['score']);
            }
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        // query: Get number of companies per fund per accusation category
        $queryBuilder->select(
            'f.id, ' .
            'count(DISTINCT sc.name) as company_count, ' .
            'ac.id as ac_id'
        )
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->join('s.shareCompany', 'sc')
            ->join('sc.accusations', 'sca')
            ->join('sca.category', 'ac')
            ->where(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->in('f.id', array_keys($fundMap)),
                    $queryBuilder->expr()->in('ac.name',
                        array("Fossila bränslen",
                         "Förbjudna vapen",
                         "Alkohol", "Tobak", "Spel")
                    )
                )
            )
            ->groupBy('f.id')
            ->addGroupBy('ac.id');


        // map the company accusation count to respective fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->fillMeasure($cv['ac_id'], $cv['company_count']);
            }
        }

        //echo \Doctrine\Common\Util\Debug::dump($funds);

        return $funds;
    }
}
