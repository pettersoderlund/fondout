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

    public function findAllFunds($category = array())
    {
        $funds = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('f, fi')
            ->from('Fund\Entity\Fund', 'f')
            ->join('f.fundInstances', 'fi')
            ->getQuery()
            ->getResult();

        return $this->mapControversialMarketValues($funds, $category);
    }

    protected function mapControversialMarketValues(array $funds, $category = array())
    {
        $queryBuilder    = $this->getEntityManager()->createQueryBuilder();
        $subQueryBuilder = clone $queryBuilder;
        $fundMap         = array();

        foreach ($funds as $fund) {
            $fundMap[$fund->id] = $fund;
        }

        // subquery: select all distinct accusations that match the category criteria
        // and the share company ID
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

        return $funds;
    }
}
