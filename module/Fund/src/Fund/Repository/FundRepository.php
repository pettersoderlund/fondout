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
      // QUERY TO IDENTIFY THE LATEST DATE AVAILIBLE AMONG FUND INSTANCES
      // TO ONLY COMPARE AMONG THE FI's IN THIS DATE.
      return $this->getEntityManager()
          ->createQueryBuilder()
          ->select('MAX(fi0.date)')
          ->from('Fund\Entity\FundInstance', 'fi0');
    }

    public function getOldFIDateSubQ($monthlag)
    {
      // QUERY TO IDENTIFY THE LATEST DATE AVAILIBLE AMONG FUND INSTANCES
      // TO ONLY COMPARE AMONG THE FI's IN THIS DATE.
      return $this->getEntityManager()
          ->createQueryBuilder()
          ->select("DATE_SUB(MAX(fi1.date), " . $monthlag . ", 'month')")
          ->from('Fund\Entity\FundInstance', 'fi1');
    }

    /* This function is REALLY SLOW ~0.3 seconds. */
    public function findControversialCompanies(Fund $fund, $accCategory)
    {
        /*
        This was used for sc.id in subq earlier. Very slow with large tables.
        New version does not allow the same sc to have several accusations in
        the same category.

        $subqb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('scc.id')
            ->from('Fund\Entity\ShareCompany', 'scc')
            ->join('scc.accusations', 'accusations')
            ->join('accusations.category', 'accusation_category')
            ->where('accusation_category.name = ?2')
            ->distinct();
        */

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

    public function countShares(Fund $fund)
    {
         $qb =  $this->getEntityManager()
            ->createQueryBuilder();

          $qb->select('COUNT(DISTINCT s.isin)')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->where('f.name = ?1')
            ->andWhere($qb->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()))
            ->andWhere('sh.marketValue > 0');


        $qb->setParameter(1, $fund->name);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllFunds()
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $dql = $qb->select('f, c, fi, fc, fm, b')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->leftJoin('f.measures', 'fm')
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

        /*
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
        */

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
            ->andWhere($queryBuilder->expr()->in('fi.date', $this->getCurrentFIDateSubQ()->getDql()))
            ->groupBy('f.id')
            ->addGroupBy('ac.id');

        // map the company accusation count to respective fund
        foreach ($queryBuilder->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->fillMeasure($cv['ac_id'], $cv['company_count']);
            }
        }


        // NAV calculations
        //$conn = $this->getServiceLocator()->get('doctrine.connection.orm_default');
        //$conn = $this->getConnection();
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


        /*$conn = $this->getEntityManager()->getConnection();
        $sql =
        "select f.id as id, fi.net_asset_value as nav " .
        "from fund f " .
        "join fund_instance fi on fi.fund = f.id  " .
        "where date = (select DATE_ADD(max(date), INTERVAL -1 year) from fund_instance) " .
        "and fi.net_asset_value != 0";
        $stmt = $conn->query($sql); // Simple, but has several drawbacks

        // map the company accusation count to respective fund
        foreach ($stmt->fetchAll() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setNav1year($cv['nav']);
            }
        }
        */
        /*
        $sql =
        "select f.id as id, fi.net_asset_value as nav " .
        "from fund f " .
        "join fund_instance fi on fi.fund = f.id  " .
        "where date = (select DATE_ADD(max(date), INTERVAL -3 year) from fund_instance) " .
        "and fi.net_asset_value != 0";
        $stmt = $conn->query($sql); // Simple, but has several drawbacks

        // map the company accusation count to respective fund
        foreach ($stmt->fetchAll() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setNav3year($cv['nav']);
            }
        }


        $sql =
        "select f.id as id, fi.net_asset_value as nav " .
        "from fund f " .
        "join fund_instance fi on fi.fund = f.id  " .
        "where date = (select DATE_ADD(max(date), INTERVAL -5 year) from fund_instance) " .
        "and fi.net_asset_value != 0";
        $stmt = $conn->query($sql); // Simple, but has several drawbacks

        // map the company accusation count to respective fund
        foreach ($stmt->fetchAll() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setNav5year($cv['nav']);
            }
        }
        */


        //echo \Doctrine\Common\Util\Debug::dump($this);

        return $funds;
    }


    // Return array of all funds with nav from now minus $months
    public function findNavDiffAllFunds($months) {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->select('f.id as id, fi.netAssetValue as nav')
      ->from('Fund\Entity\Fund', 'f')
      ->join('f.fundInstances', 'fi')
      ->where($queryBuilder->expr()->in('fi.date', $this->getOldFIDateSubQ($months)->getDql()))
      ->andWhere($queryBuilder->expr()->neq('fi.netAssetValue', 0));

      return $queryBuilder->getQuery()->getResult();

    }
}
