<?php

namespace Fund\Repository;

use Doctrine\ORM\EntityRepository;

use Fund\Entity\Fund;

/**
* FundRepository
*/
class FundRepository extends EntityRepository
{

    /* This function is REALLY SLOW ~0.3 seconds. */
    /*
    * Find a list of the controversial companies w/ corresponding percent
    * of fund size of the company.
    */
    public function findControversialCompanies(Fund $fund, $accCategory)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('DISTINCT(sc.name) as name,
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
              // Get all funds individual latest fundinstance
              ->andWhere($qb->expr()->eq(
                'fi.date',
                '(select max(fi2.date) '
                  . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
                )
              )
              ->andWhere('accusation_category.name = ?2')
              ->groupBy('sc.name')
              ->groupBy('accusations.id')
              ->orderBy('part', 'desc')
              ->setParameter(1, $fund->name)
              ->setParameter(2, $accCategory->name);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /*
    * Get a list of all active funds that has got a
    * - fund instance
    * - fondoutcategory
    * - fi dated more recently than 6 months prior the newest active fi
    */
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
            // Get all funds individual latest fundinstance
            ->andWhere($qb->expr()->eq(
              'fi.date',
              '(select max(fi2.date) '
                . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
              )
            )
            // Only include latest six months
            ->andWhere(
              "fi.date > (select DATE_SUB(MAX(fi3.date), 6, 'month') "
              . " from Fund\Entity\FundInstance fi3)"
            );

        $funds = $dql->getQuery()->getResult();

        return $funds;
    }


    /*
    * Map all kinds of values to the fund.
    * - nav and date for the latest, individual, fund instance
    * - controversial company counts
    * - nav performance 1 3 5 years
    *
    * TODO : map controversial company percentage shares of fund
    */
    public function mapControversialMarketValues($funds)
    {
        if ($funds instanceof Fund) {
            $funds = array($funds);
        }

        if (!is_array($funds)) {
            throw new \InvalidArgumentException();
        }

        $qb    = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder = clone $qb;
        $fundMap         = array();

        foreach ($funds as $fund) {
            $fund->setTotalMarketValue();
            $fundMap[$fund->id] = $fund;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        // query: nav date all active funds
        $qb->select(
            'f.id, ' .
            'fi.date as date, ' .
            'fi.netAssetValue as nav'
        )
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->join('fi.shareholdings', 'sh')
            ->join('sh.share', 's')
            ->where($qb->expr()->in('f.id', array_keys($fundMap)))
            // Get all funds individual latest fundinstance
            ->andWhere($qb->expr()->eq(
              'fi.date',
              '(select max(fi2.date) '
                . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
              )
            );

        // map the fund values nav date
        foreach ($qb->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->setNav($cv['nav']);
                $fundMap[$cv['id']]->setDate($cv['date']);
            }
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        // query: Get number of companies per fund per accusation category
        $qb->select(
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
                $qb->expr()->andx(
                    $qb->expr()->in('f.id', array_keys($fundMap)),
                    $qb->expr()->in('ac.name',
                        array("Fossila bränslen",
                         "Förbjudna vapen",
                         "Alkohol", "Tobak", "Spel")
                    )
                )
            )
            // Get all funds individual latest fundinstance
            ->andWhere($qb->expr()->eq(
              'fi.date',
              '(select max(fi2.date) '
                . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
              )
            )
            ->andWhere('sh.marketValue > 0')
            ->groupBy('f.id')
            ->addGroupBy('ac.id');


        // map the company accusation count to respective fund
        foreach ($qb->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
                $fundMap[$cv['id']]->fillMeasure($cv['ac_id'], $cv['company_count']);
            }
        }



        // START Percentage calculations -----------------------

                $qb = $this->getEntityManager()->createQueryBuilder();
                // query: Get percentage of companies per fund per accusation category
                $qb->select(
                    'f.id, ' .
                    'sum(sh.marketValue)/fi.totalMarketValue as percentage, ' .
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
                        $qb->expr()->andx(
                            $qb->expr()->in('f.id', array_keys($fundMap)),
                            $qb->expr()->in('ac.name',
                                array("Fossila bränslen",
                                 "Förbjudna vapen",
                                 "Alkohol", "Tobak", "Spel")
                            )
                        )
                    )
                    // Get all funds individual latest fundinstance
                    ->andWhere($qb->expr()->eq(
                      'fi.date',
                      '(select max(fi2.date) '
                        . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
                      )
                    )
                    ->andWhere('sh.marketValue > 0')
                    ->groupBy('f.id')
                    ->addGroupBy('ac.id');


                // map the company accusation count to respective fund
                foreach ($qb->getQuery()->getResult() as $cv) {
                    if (isset($fundMap[$cv['id']])) {
                        $fundMap[$cv['id']]->fillMeasurePercent($cv['ac_id'], $cv['percentage']);
                    }
                }

























        // END Percentage calculations -----------------------

        // NAV calculations
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($qb->expr()->eq('fi.date', "(select DATE_SUB(max(fi2.date), 12, 'month') "
          . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'))
        ->andWhere($qb->expr()->neq('fi.netAssetValue', 0));

        foreach ($qb->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav1year($cv['nav']);
            }
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($qb->expr()->eq('fi.date', "(select DATE_SUB(max(fi2.date), 36, 'month') "
          . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'))
        ->andWhere($qb->expr()->neq('fi.netAssetValue', 0));

        foreach ($qb->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav3year($cv['nav']);
            }
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('f.id as id, fi.netAssetValue as nav')
        ->from('Fund\Entity\Fund', 'f')
        ->join('f.fundInstances', 'fi')
        ->where($qb->expr()->eq('fi.date', "(select DATE_SUB(max(fi2.date), 60, 'month') "
          . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'))
        ->andWhere($qb->expr()->neq('fi.netAssetValue', 0));

        foreach ($qb->getQuery()->getResult() as $cv) {
            if (isset($fundMap[$cv['id']])) {
              $fundMap[$cv['id']]->setNav5year($cv['nav']);
            }
        }

        //echo \Doctrine\Common\Util\Debug::dump($this);

        return $funds;
    }

    public function findActiveFundcompanies()
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $dql = $qb->select('c')
            ->from('Fund\Entity\FundCompany', 'c')
            ->join('c.funds', 'f')
            ->join('f.fundInstances', 'fi')
            ->where('f.active = 1')
            // Get all funds individual latest fundinstance
            ->andWhere($qb->expr()->eq(
              'fi.date',
              '(select max(fi2.date) '
                . ' from Fund\Entity\FundInstance fi2 where fi2.fund = f.id)'
              )
            );

        $fundcompanies = $dql->getQuery()->getResult();

        return $fundcompanies;
    }

}
