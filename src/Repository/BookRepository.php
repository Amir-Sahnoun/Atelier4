<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function searchBookByRef($ref)
{
    return $this->createQueryBuilder('b')
        ->where('b.ref = :ref')
        ->setParameter('ref', $ref)
        ->getQuery()
        ->getResult();
}

    public function findPublishedBooksByAuthors()
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->orderBy('a.username', 'ASC') // Tri par ordre alphabétique de l'auteur
            ->getQuery()
            ->getResult();
    }

    public function findPublishedBooksBefore2023WithMoreThan10Books()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('b')
            ->from('App\Entity\Book', 'b')
            ->join('b.author', 'a')
            ->where('b.publicationDate < :date')
            ->andWhere('a.id IN (
                SELECT a2.id
                FROM App\Entity\Author a2
                JOIN a2.books b2
                GROUP BY a2.id
                HAVING COUNT(b2) > 10
            )')
            ->setParameter('date', new \DateTime('2023-01-01'))
            ->getQuery();

        return $query->getResult();
    }

    public function updateScienceFictionToRomance()
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();
        $qb
            ->update('App\Entity\Book', 'b')
            ->set('b.category', ':newCategory')
            ->where('b.category = :oldCategory')
            ->setParameter('oldCategory', 'Science-Fiction')
            ->setParameter('newCategory', 'Romance');

        return $qb->getQuery()->execute();
    }

    public function countBooksInRomanceCategory()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT COUNT(b) 
            FROM App\Entity\Book b
            WHERE b.category = :category
        ');
        $query->setParameter('category', 'Romance');

        return $query->getSingleScalarResult();
    }
    
    public function findBooksPublishedBetweenDates()
    {
        $entityManager = $this->getEntityManager();

        $dql = 'SELECT b FROM App\Entity\Book b ' .
               'WHERE b.publicationDate >= :startDate ' .
               'AND b.publicationDate <= :endDate';

        $query = $entityManager->createQuery($dql)
            ->setParameter('startDate', new \DateTime('2014-01-01'))
            ->setParameter('endDate', new \DateTime('2018-12-31'));

        return $query->getResult();
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
