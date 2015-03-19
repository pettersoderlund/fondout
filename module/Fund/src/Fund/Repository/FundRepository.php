<?php

namespace Fund\Repository;

use Doctrine\ORM\EntityRepository;

use Fund\Entity\Fund;
use Fund\Entity\ControversialValue;

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

    public function findControversialCompanies(Fund $fund, $category = array())
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('sc, b, accusation_category')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.accusations', 'b')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->join('b.category', 'accusation_category')
            ->orderBy('sc.name', 'ASC')
            ->where('f.name = ?1')
            ->andWhere('sh.marketValue > 0')
            ->setParameter(1, $fund->name)
            ->distinct();

        if (count($category) > 0) {
            $qb->andWhere(
                $qb->expr()->in('accusation_category.id', $category)
            );
        }

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

    public function findBanks(Fund $fund)
    {
         $qb=  $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b.name as name, bf.url')
            ->from('Fund\Entity\BankFundListing', 'bf')
            ->join('bf.bank', 'b')
            ->join('bf.fund', 'f')
            ->where('f.id = ?1');

        $qb->setParameter(1, $fund->id);
        return $qb->getQuery()->getResult();
    }

    public function findAverageCo2Category(Fund $fund)
    {
      $qb=  $this->getEntityManager()
      ->createQueryBuilder()
      ->select(
          'SUM((sh.marketValue/sc.marketValueSEK)*e.scope12)/SUM(sh.marketValue)*1000000,
          SUM(sh.marketValue)/SUM(DISTINCT fi.totalMarketValue)'
        )
      ->from('Fund\Entity\Fund', 'f')
      ->join('f.fundInstances', 'fi')
      ->join('fi.shareholdings', 'sh')
      ->join('sh.share', 's')
      ->join('s.shareCompany', 'sc')
      ->leftJoin('sc.emissions', 'e')
      ->where('f.fondoutcategory = ?1')
      ->andWhere('e.date is not null')
      ->andWhere('sc.marketValueSEK is not null')
      ->andWhere('e.scope12 is not null')
      ->andWhere('f.active=1');

      $qb->setParameter(1, $fund->fondoutCategory->id);


      return $qb->getQuery()->getResult();

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
            ->orderBy('c.name', 'DESC')
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
        // NOTE 28/3 2015: Should be DISTINCT a.accusationCategory
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
        // query: add scope12 for all holdings wieghted to fund size and share proportion
        // Also add the coverage for the fund of co2 emissions that we know of
        $queryBuilder->select(
            'f.id, ' .
            //'(sum((sh.marketValue/sc.marketValueSEK)*e.scope12)/fi.totalMarketValue)*1000000 as scope12weighted, ' .
            '(sum((sh.marketValue/sc.marketValueSEK)*e.scope12)/sum(sh.marketValue))*1000000 as scope12weighted, ' .
            'sum(sh.marketValue)/fi.totalMarketValue as coverage'
        )
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->join('s.shareCompany', 'sc')
            ->join('sc.emissions', 'e')
            ->where($queryBuilder->expr()->in('f.id', array_keys($fundMap)))
            ->groupBy('f.id');

        // map the co2 value and co2coverage to the related fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setCo2($cv['scope12weighted']);
                $fundMap[$cv['id']]->setCo2Coverage($cv['coverage']);
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
                         "Kontroversiella vapen", 
                         "Alkohol, tobak, spel")
                    )
                )
            )
            ->groupBy('f.id')
            ->addGroupBy('ac.id');


        // map the co2 value and co2coverage to the related fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->fillMeasure($cv['ac_id'], $cv['company_count']);
            }
        }

        //echo \Doctrine\Common\Util\Debug::dump($funds);

        return $funds;
    }
}
