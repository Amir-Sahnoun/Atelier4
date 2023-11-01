<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/books', name: 'book_list')]
    public function fetchBooks(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Book::class);
        $publishedBooks = $repository->findBy(['published' => true]);

        // Calculate the number of non-published books
        $allBooks = $repository->findAll();
        $nonPublishedBooksCount = count($allBooks) - count($publishedBooks);

        return $this->render('book/books.html.twig', [
            'publishedBooks' => $publishedBooks,
            'nonPublishedBooksCount' => $nonPublishedBooksCount,
        ]);
    }
    #[Route('/books/add', name: 'book_add')]
    public function addBook(Request $request, EntityManagerInterface $entityManager, AuthorRepository $authorRepository): Response
    {
        $book = new Book();

        // Create the form
        $form = $this->createForm(BookType::class, $book);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Fetch the author from the database (replace 1 with the actual author ID)
            $author = $authorRepository->find(1);

            // Increment the 'nb_books' attribute of the Author entity
            $author->setNbBooks($author->getNbBooks() + 1);

            // Associate the book with the author
            $book->setAuthor($author);

            $entityManager->persist($book);
            $entityManager->persist($author);
            $entityManager->flush();
            return $this->redirectToRoute('book_list');
        }

        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $mr, BookRepository $repo, $id, Request $request): Response {
        $book = $repo->find($id);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $mr->getManager();
            $em->flush(); 
            return $this->redirectToRoute('book_list');
        }
    
        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/books/remove/{id}', name: 'book_remove')]
public function remove(BookRepository $repo,$id, EntityManagerInterface $em): Response
{
    $book=$repo->find($id);
    $em->remove($book);
    $em->flush();

    return $this->redirectToRoute('book_list');
}
#[Route('/books/show/{id}', name: 'book_show')]
public function showBook(Book $book): Response
{
    return $this->render('book/show.html.twig', [
        'book' => $book,
    ]);
}
}
