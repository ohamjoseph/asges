<?php

namespace App\Repository;

use App\Entity\Adhesion;
use App\Entity\Association;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Adhesion>
 *
 * @method Adhesion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adhesion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adhesion[]    findAll()
 * @method Adhesion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdhesionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adhesion::class);
    }

    public function add(Adhesion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Adhesion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /* Recupération de l'adhesion associer a un utilisateur et une association donnée */
    /**
     * @param Association $association
     * @param User $user
     * @return Adhesion
     *
     */
    public function userAdhesion(Association $association, User $user):Adhesion
    {
        return $this->findOneBy([
            'user'=>$user,
            'association'=>$association
        ]);
    }


    /**
     * @param User $user
     * @return Adhesion[]
     */
    public function userAdhesions(User $user):array
    {
        return $this->findBy([
            'user'=>$user,
            'status'=>'ACTIVE'
        ]);
    }

    public function userAdhesionsNCreer(User $user):ArrayCollection
    {
        $critere = new Criteria();
        $critere->where(Criteria::expr()->neq('status','CREER'));
        return $this->matching($critere);
    }

    public function userAdhesionsSuppend(User $user):array
    {
        return $this->findBy([
            'user'=>$user,
            'status'=>'SUSPENDUS'
        ]);
    }

    public function userAdhesionsCreer(User $user):array
    {
        return $this->findBy([
            'user'=>$user,
            'status'=>'CREER'
        ]);
    }

//    public function findByExampleField(Association $association, User $): ?Adhesion
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }



//    /**
//     * @return Adhesion[] Returns an array of Adhesion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Adhesion
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
