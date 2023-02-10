<?php

declare(strict_types=1);

namespace App\Controller\Front;

use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\data\SearchData;
use App\Form\SearchForm;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('front/home/index.html.twig',);
    }

    /**
     * Cette fonction permet à l'utilisateur d'effectuer une recherche par mots-clés.
     * 
     * @Route("/blog", name="blog", methods={"GET", "POST"})
     */
    public function searchAndDisplayArticles(ArticleRepository $articleRepository, Request $request): Response
    {
        // Initialisation des données de recherche
        $data = new SearchData();

        // Récupération de la page courante
        $data->page = $request->get('page', 1);

        // Création du formulaire de recherche à partir des données
        $form = $this->createForm(SearchForm::class, $data);

        // Traitement des données soumises dans le formulaire
        $form->handleRequest($request);

        // Recherche des articles en utilisant les données de recherche
        $articles = $articleRepository->findSearch($data);

        // Retour de la vue pour afficher les articles trouvés
        return $this->render('front/blog/blog.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }

    /**
     * Cette fonction permet à l'utilisateur d'afficher un article et de pouvoir le commenter
     * et répondre à des commentaires.
     * 
     * @Route("/blog/{id}", name="blog_show", methods={"GET", "POST"})
     */
    public function showAndCommentArticle(Article $article, Request $request, EntityManagerInterface $Manager): Response
    {

        //partie commentaires
        $comment = new Comment();

        $commentForm = $this->createform(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new DateTime());
            $comment->setArticle($article);

            // On récupère le contenu du champ parentid
            $parentid = $commentForm->get("parentid")->getData();

            //on va chercher le commentaire correspondant
            if ($parentid != null) {
                $parent = $Manager->getRepository(Comment::class)->find($parentid);
            }

            $comment->setParent($parent ?? null);
            $Manager->persist($comment);
            $Manager->flush();

            $this->addFlash('message', 'Votre commentaire à bien été envoyé');
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }


        return $this->render('front/blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $commentForm->createView()
        ]);
    }
}
