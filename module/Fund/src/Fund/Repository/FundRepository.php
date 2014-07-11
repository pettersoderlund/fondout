<?php

namespace Fund\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function findControversialCompanies(Fund $fund, $criteria = array())
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('sc')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.blacklists', 'b')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->where('f.name = ?1')
            ->setParameter(1, $fund->name)
            ->distinct();


        // if($organizations) {
        //     $qb->andWhere($qb->expr()->in('b.sourceOrganization', '?2'))
        //         ->setParameter(2, $organizations);
        // }
        // if($categories) {
        //     $qb->andWhere($qb->expr()->in('b.category', '?3'))
        //         ->setParameter(3, $categories);
        // }

        $qb->orderBy('sc.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findSimilarFunds(Fund $fund, $categories, $organizations)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $rsm = new ResultSetMapping;
        $rsm->addEntityResult('\Fund\Entity\SimilarFundScore', 'f1');
        $rsm->addFieldResult('f1', 'id', 'id');
        $rsm->addFieldResult('f1', 'blacklistMarketValue', 'blacklistMarketValue');
        $rsm->addFieldResult('f1', 'fundMarketValue', 'fundMarketValue');

        $sql = "SELECT f1.id, blacklistMarketValue, fundMarketValue " .
            "FROM funds f1 " .
            "LEFT OUTER JOIN ( " .

            "SELECT f.name as fundname0, sum(sh.market_value) AS blacklistMarketValue, fi.total_market_value AS fundMarketValue " .
            "FROM funds f " .
            "JOIN fund_instances fi ON f.id = fi.fund " .
            "JOIN shareholdings sh ON fi.id = sh.fund_instance " .
            "JOIN shares s ON sh.share = s.id " .
            "JOIN share_companies sc ON s.share_company = sc.id " .
            "JOIN (select distinct share_company AS bl_sc, category, source_organization from blacklist b) B0 ON sc.id = B0.bl_sc " .
            "WHERE mainCategory = ? " .
            "AND B0.category IN (?) " .
            "AND B0.source_organization IN (?) " .
            "GROUP BY f.name " .

            ") B0 " .
            "ON f1.name=B0.fundname0 " .
            "WHERE mainCategory = ? " .
            "ORDER BY blacklistMarketValue ASC LIMIT 3";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $fund->getMainCategory());
        $query->setParameter(2, $categories);
        $query->setParameter(3, $organizations);
        $query->setParameter(4, $fund->getMainCategory());

        $result = $query->getResult();


        return $result;
    }
}
