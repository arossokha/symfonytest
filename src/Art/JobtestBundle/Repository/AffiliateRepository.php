<?php

namespace Art\JobtestBundle\Repository;
use Doctrine\ORM\NoResultException;

/**
 * AffiliateRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AffiliateRepository extends \Doctrine\ORM\EntityRepository
{
    public function getForToken($token)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.is_active = :active')
            ->setParameter('active',1)
            ->andWhere('a.token = :token')
            ->setParameter('token',$token)
            ->setMaxResults(1)
        ;

        try {
            $affiliate = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e){
            $affiliate = null;
        }

        return $affiliate;
    }
}