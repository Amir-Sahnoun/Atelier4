<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 *
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }
    
    public function listAuthorByEmail()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT a FROM App\Entity\Author a ORDER BY a.email ASC'
        );

        return $query->getResult();
    }

    public function findAuthorsByBookCount($minBooks, $maxBooks)
    {
        $entityManager = $this->getEntityManager();
        $dql = "
            SELECT a
            FROM App\Entity\Author a
            LEFT JOIN a.books b
            GROUP BY a.id
            HAVING COUNT(b) >= :minBooks AND COUNT(b) <= :maxBooks
        ";

        $query = $entityManager->createQuery($dql)
            ->setParameter('minBooks', $minBooks)
            ->setParameter('maxBooks', $maxBooks);

        return $query->getResult();
    }
    public function deleteAuthorsWithNoBooks()
    {
        $entityManager = $this->getEntityManager();

        $dql = "DELETE FROM App\Entity\Author a WHERE NOT EXISTS (SELECT b FROM App\Entity\Book b WHERE b.author = a)";
        $query = $entityManager->createQuery($dql);

        return $query->execute();
    }
//    /**
//     * @return Author[] Returns an array of Author objects
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

//    public function findOneBySomeField($value): ?Author
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
