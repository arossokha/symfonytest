<?php

namespace Art\JobtestBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Art\JobtestBundle\Entity\Job;

/**
 * JobRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class JobRepository extends EntityRepository
{
    public function getActiveJobs($category_id = null, $max = null, $offset = null, $affiliate_id = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.expires_at > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
            ->andWhere('j.is_activated = :activated')
            ->setParameter('activated', 1)
            ->orderBy('j.expires_at', 'DESC');
        if ($max) {
            $qb->setMaxResults($max);
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }
        if ($category_id) {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $category_id);
        }
        if ($affiliate_id) {
            $qb->leftJoin('j.category', 'c')
                ->leftJoin('c.affiliates', 'a')
                ->andWhere('a.id = :affiliate_id')
                ->setParameter('affiliate_id', $affiliate_id);
        }
        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getActiveJob($id)
    {
        $query = $this->createQueryBuilder('j')
            ->where('j.id = :id')
            ->setParameter('id', $id)
            ->andWhere('j.expires_at > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
            ->andWhere('j.is_activated = :activated')
            ->setParameter('activated', 1)
            ->setMaxResults(1)
            ->getQuery();

        try {
            $job = $query->getSingleResult();
        } catch (DoctrineOrmNoResultException $e) {
            $job = null;
        }

        return $job;
    }

    public function countActiveJobs($category_id = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->select('count(j.id)')
            ->where('j.expires_at > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
            ->andWhere('j.is_activated = :activated')
            ->setParameter('activated', 1);

        if ($category_id) {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $category_id);
        }

        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    public function cleanup($days)
    {
        $query = $this->createQueryBuilder('j')
            ->delete()
            ->where('j.is_activated IS NULL')
            ->andWhere('j.created_at < :created_at')
            ->setParameter('created_at', date('Y-m-d', time() - 86400 * $days))
            ->getQuery();

        return $query->execute();
    }

    public function getLatestPost($category_id = false)
    {
        $query = $this->createQueryBuilder('j')
            ->where('j.expires_at > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
            ->andWhere('j.is_activated = :activated')
            ->setParameter('activated', 1)
            ->orderBy('j.expires_at', 'DESC')
            ->setMaxResults(1);

        if ($category_id) {
            $query->andWhere(' j.category = :category')
                ->setParameter('category', $category_id);
        }
        try {
            $job = $query->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $job = null;
        }

        return $job;
    }

    /**
     * Lucene search with zend framework
     * @param $query
     * @return array
     */
    public function getForLuceneQuery($query)
    {
        $hits = Job::getLuceneIndex()->find($query);

        $pks = [];
        foreach ($hits as $hit) {
            $pks[] = $hit->pk;
        }

        if (empty($pks)) {
            return array();
        }

        $q = $this->createQueryBuilder('j')
            ->where('j.id IN (:pks)')
            ->setParameter('pks', $pks)
            ->andWhere('j.is_activated = :active')
            ->setParameter('active', 1)
            ->setMaxResults(20)
            ->getQuery();

        return $q->getResult();
    }

    /**
     * Global search instead of lucene
     * Search fields: position, company, location, description
     * @param $query
     * @return array
     */
    public function getForSearchQuery($query)
    {
        $query = '%' . $query . '%';
        $q = $this->createQueryBuilder('j')
            ->where('j.position like :position')
            ->setParameter('position', $query)
            ->orWhere('j.company like :company')
            ->setParameter('company', $query)
            ->orWhere('j.location like :location')
            ->setParameter('location', $query)
            ->orWhere('j.description like :description')
            ->setParameter('description', $query)

            ->andWhere('j.is_activated = :active')
            ->setParameter('active', 1)
            ->setMaxResults(20)
            ->getQuery();

        return $q->getResult();
    }
}