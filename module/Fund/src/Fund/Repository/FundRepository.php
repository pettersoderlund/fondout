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

    public function findControversialCompanies(Fund $fund)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('sc')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.accusations', 'b')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->orderBy('sc.name', 'ASC')
            ->where('f.name = ?1')
            ->setParameter(1, $fund->name)
            ->distinct()
            ->getQuery()
            ->getResult();
    }


    public function findControversialValue(Fund $fund)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(sh.marketValue) as controversialValue')
            ->from('Fund\Entity\ShareCompany', 'sc')
            ->join('sc.accusations', 'b')
            ->join('sc.shares', 's')
            ->join('s.shareholdings', 'sh')
            ->join('sh.fundInstance', 'fi')
            ->join('fi.fund', 'f')
            ->where('f.name = ?1')
            ->setParameter(1, $fund->name)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function mapControversialMarketValues($funds)
    {
        // sum the marketvalue for all controversial shareholdings per fund
        $dql = "SELECT NEW ControversialValue(f.id, SUM(sh.marketValue))" .
            "FROM Fund\Entity\Fund f " .
            "JOIN f.fundInstances fi " .
            "JOIN fi.shareholdings sh " .
            "JOIN sh.share s " .
            "JOIN s.shareCompany sc ".
            "WHERE EXISTS (" .
                "SELECT DISTINCT a.accusation " .
                "FROM Fund\Entity\Accusation a " .
                "JOIN a.category c " .
                "WHERE a.shareCompany = sc.id" .
            ") " .
            "AND f.id IN(?1) " .
            "GROUP BY f.id";

        $fundMap = array();

        foreach ($funds as $fund) {
            $fundMap[$fund->id] = $fund;
        }

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, array_keys($fundMap));

        foreach ($query->getResult() as $cv) {
            if (isset($fundMap[$cv->fundId])) {
                $fundMap[$cv->fundId]->setControversialValue($cv);
            }
        }

        return $funds;
    }
}
