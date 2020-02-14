<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\Category;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{
    /**
     * @param $categoryId
     * 
     * @return Job|null
     */
    public function findActiveJobs(int $id) : ?Job
    {
        return $this->createQueryBuilder('j')
            ->where('j.id = :id')
            ->andWhere('j.expiresAt > :date')
            ->setParameter('id', $id)
            ->setParameter('date', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

   
    public function getPaginatedActiveJobsByCategoryQuery(Category $category) : AbstractQuery{
        return $this->createQueryBuilder('j')
        ->where('j.category = :category')
        ->andWhere('j.expiresAt > :date')
        ->andWhere('j.activated = :activated')
        ->setParameter('category', $category)
        ->setParameter('date', new \Datetime())
        ->setParameter('activated', true)
        ->getQuery();
    }
}

?>
