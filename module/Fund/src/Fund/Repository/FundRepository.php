<?php

namespace Fund\Repository;

use Doctrine\ORM\EntityRepository;

use Fund\Entity\Fund;

/**
* FundRepository
*/
class FundRepository extends EntityRepository
{

    public function getCurrentFIDateSubQ()
    {
      // QUERY TO IDENTIFY THE LATEST DATE AVAILIBLE AMONG _ALL_ FUND INSTANCES
      return $this->getEntityManager()
          ->createQueryBuilder()
          ->select('MAX(fi0.date)')
          ->from('Fund\Entity\FundInstance', 'fi0');
    }

    public function getOldFIDateSubQ($monthlag)
    {
      // QUERY TO IDENTIFY THE A DATE PRIOR TO THE LATEST DATE
      // AVAILIBLE AMONG _ALL_ FUND INSTANCES
      return $this->getEntityManager()
          ->createQueryBuilder()
          ->select("DATE_SUB(MAX(fi1.date), " . $monthlag . ", 'month')")
          ->from('Fund\Entity\FundInstance', 'fi1');
    }

    /* This function is REALLY SLOW ~0.3 seconds. */
    public function findControversialCompanies(Fund $fund, $accCategory)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('sc.name as name,
              SUM(sh.marketValue)/fi.totalMarketValue as part')
              ->from('Fund\Entity\ShareCompany', 'sc')
              ->join('sc.shares', 's')
              ->join('s.shareholdings', 'sh')
              ->join('sh.fundInstance', 'fi')
              ->join('fi.fund', 'f')
              ->leftJoin('sc.accusations', 'accusations')
              ->leftJoin('accusations.category', 'accusation_category')
              ->orderBy('sc.name', 'ASC')
              ->where('f.name = ?1')
              ->andWhere('sh.marketValue > 0')
              ->andWhere($qb->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()))
              ->andWhere('accusation_category.name = ?2')
              ->groupBy('sc.name')
              ->orderBy('part', 'desc')
              ->setParameter(1, $fund->name)
              ->setParameter(2, $accCategory->name);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findAllFunds()
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $dql = $qb->select('f, c, fi, fc, b')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->leftJoin('f.banks', 'b')
            ->join('f.company', 'c')
            ->join('f.fondoutcategory', 'fc')
            ->orderBy('f.name', 'ASC')
            ->where('f.active = 1')
            ->andWhere($qb->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()));

        $funds = $dql->getQuery()->getResult();

        return $funds;
    }

    /**
    *
    * Second argument is an array of sustainability categories identified by id.
    */
    public function mapControversialMarketValues($funds)
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


        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        // query: nav date all active funds
        $queryBuilder->select(
            'f.id, ' .
            'fi.date as date, ' .
            'fi.netAssetValue as nav'
        )
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->where($queryBuilder->expr()->in('f.id', array_keys($fundMap)))
            ->andWhere($queryBuilder->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()));

        // map the fund values nav date
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setNav($cv['nav']);
                $fundMap[$cv['id']]->setDate($cv['date']);
            }
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        // query: Get number of companies per fund per accusation category
        $queryBuilder->select(
            'f.id, ' .
            'count(DISTINCT sc.name) as company_count, ' .
            'ac.id as ac_id',
            'fi.date as date',
            'fi.netAssetValue as nav'
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
            ->andWhere($queryBuilder->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()))
            ->andWhere('sh.marketValue > 0')
            ->groupBy('f.id')
            ->addGroupBy('ac.id');

        // map the company accusation count to respective fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->fillMeasure($cv['ac_id'], $cv['company_count']);
            }
        }

        // NAV calculations
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($queryBuilder->expr()->in('fi.date', $this->getOldFIDateSubQ(12)->getDql()))
        ->andWhere($queryBuilder->expr()->neq('fi.netAssetValue', 0));

        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav1year($cv['nav']);
            }
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($queryBuilder->expr()->in('fi.date', $this->getOldFIDateSubQ(36)->getDql()))
        ->andWhere($queryBuilder->expr()->neq('fi.netAssetValue', 0));

        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav3year($cv['nav']);
            }
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($queryBuilder->expr()->in('fi.date', $this->getOldFIDateSubQ(60)->getDql()))
        ->andWhere($queryBuilder->expr()->neq('fi.netAssetValue', 0));

        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav5year($cv['nav']);
            }
        }

        //echo \Doctrine\Common\Util\Debug::dump($this);

        return $funds;
    }

}
