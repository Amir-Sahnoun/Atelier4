<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'author_list')]
    public function listAuthors(AuthorRepository $repo): Response
    {
        $authors = $repo->findAll();
        return $this->render('author/list.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(ManagerRegistry $mr,AuthorRepository $repo):Response {
        $s=new Author();
        $c=$repo->find(1);
        $s->setUsername('test');
        $s->setEmail('test@gmail.com');
        $em=$mr->getManager();
        $em->persist($s);
        $em->flush();
        return $this->redirectToRoute('author_list');
    }
    
    #[Route('/authors/create', name: 'author_create')]
    public function createAuthor(ManagerRegistry $mr,AuthorRepository $repo,Request $request): Response
    {
        $author = new Author();

        // Créez un formulaire basé sur le AuthorType
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et valide, persistez l'auteur dans la base de données
            $em = $mr->getManager();
            $em->persist($author);
            $em->flush();

            // Redirigez l'utilisateur vers la liste des auteurs ou une autre page
            return $this->redirectToRoute('author_list');
        }

        return $this->render('author/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(AuthorRepository $repo,$id, EntityManagerInterface $em):Response{
        $s=$repo->find($id);
        $em->remove($s);
        $em->flush();
    
        return $this->redirectToRoute('author_list');
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $mr, AuthorRepository $repo, $id, Request $request): Response {
        $author = $repo->find($id);
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $mr->getManager();
            $em->flush(); 
            return $this->redirectToRoute('author_list');
        }
    
        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
