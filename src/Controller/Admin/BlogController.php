<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\data\SearchData;
use App\Form\SearchForm;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BlogController extends AbstractController
{
    /**
     * Cette fonction permet d'effectuer une recherche ciblé par rapport à des mots clés sur des articles,
     * et de les afficher.
     * 
     * @Route("/admin/blog", name="admin_blog", methods={"GET", "POST"})
     */
    public function searchAndDisplayArticles(ArticleRepository $articleRepository, Request $request): Response
    {
        // Création d'un nouvel objet SearchData
        $data = new SearchData();

        // Récupération de la page actuelle depuis la requête HTTP, ou définition de la page 1 par défaut
        $data->page = $request->get('page', 1);

        // Création d'un formulaire à partir de la classe SearchForm, en utilisant l'objet $data comme données
        $form = $this->createForm(SearchForm::class, $data);

        // Traitement de la requête HTTP pour remplir les données du formulaire
        $form->handleRequest($request);

        // Recherche des articles correspondants aux critères de recherche
        $articles = $articleRepository->findSearch($data);

        // Rendu de la vue 'admin/blog/blog.html.twig' en passant les articles et le formulaire
        return $this->render('admin/blog/blog.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }


    /**
     * Cette fonction permet de créer ou de modifier un article.
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("admin/blog/new", name="admin_blog_create", methods={"GET", "POST"})
     * @Route("admin/blog/{id}/edit", name="admin_blog_edit", methods={"GET", "POST"})
     */
    public function CreateOrEditArticle(Article $article = null, Request $request, EntityManagerInterface $entityManager)
    {
        // Création d'une nouvelle instance d'Article si aucun article n'a été transmis en paramètre
        if (!$article) {
            $article = new Article();
        }

        // Création d'un formulaire pour l'article avec les champs définis dans ArticleType
        $form = $this->createForm(ArticleType::class, $article);

        // Traitement des données envoyées à partir du formulaire
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }

            // Enregistrement de l'article dans la base de données
            $entityManager->persist($article);
            $entityManager->flush();

            // Redirection vers la page de visualisation de l'article
            return $this->redirectToRoute('admin_blog_show', ['id' => $article->getId()]);
        }

        // Affichage de la vue pour créer ou éditer un article avec le formulaire
        return $this->render('admin/blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null,
        ]);
    }


    /**
     * Cette fonction permet d'afficher un article et de laisser un commentaire sur cet article. 
     * Elle traite les données du formulaire de commentaires et enregistre le nouveau commentaire dans la base de données. 
     * Elle informe l'utilisateur que le commentaire a été envoyé avec succès et redirige vers la vue de l'article pour afficher les commentaires.
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("admin/blog/{id}", name="admin_blog_show", methods={"GET", "POST"})
     */
    public function showAndCommentArticle(Article $article, Request $request, EntityManagerInterface $Manager): Response
    {
        // Création d'une nouvelle instance d'un objet Comment.
        $comment = new Comment();

        // Création d'un formulaire pour les commentaires en utilisant le type de formulaire CommentType et en associant l'objet Comment à ce formulaire.
        $commentForm = $this->createform(CommentType::class, $comment);

        // Traitement de la requête pour remplir les données du formulaire avec les données envoyées.
        $commentForm->handleRequest($request);

        // Vérification si le formulaire a été soumis et si les données sont valides.
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            // Définition de la date de création du commentaire.
            $comment->setCreatedAt(new DateTime());
            //Définition de l'article associé au commentaire.
            $comment->setArticle($article);

            // Récupération de l'identifiant du commentaire parent à partir du formulaire.
            $parentid = $commentForm->get("parentid")->getData();

            // Permet de chercher le commentaire correspondant.
            if ($parentid != null) {
                $parent = $Manager->getRepository(Comment::class)->find($parentid);
            }

            // Définit le commentaire parent pour le nouveau commentaire.
            $comment->setParent($parent ?? null);

            $Manager->persist($comment);
            $Manager->flush();

            $this->addFlash('message', 'Votre commentaire à bien été envoyé');
            return $this->redirectToRoute('admin_blog_show', ['id' => $article->getId()]);
        }

        return $this->render('admin/blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $commentForm->createView()
        ]);
    }


    /**
     * Cette fonction permet de supprimer un article.
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("admin/blog/{id}/supprimer", name="admin_blog_remove")
     */
    public function removeArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager): Response
    {
        // Trouve l'article concerné
        $article = $articleRepository->find($id);

        $manager->remove($article);
        $manager->flush();

        $this->addFlash('message', 'Votre article a été supprimé');

        return $this->redirectToRoute('blog');
    }

    /**
     * Cette fonction permet de supprimer un commentaire enfant.
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("admin/reply/{id}/delete", name="delete_reply", methods={"POST"})
     */
    public function deleteReply(Comment $reply, EntityManagerInterface $manager)
    {
        // Cette ligne récupère le commentaire parent du commentaire de réponse actuel.
        $parent = $reply->getParent();

        // Cette condition vérifie si un commentaire parent existe. Si ce n'est pas le cas, une exception est levée avec un message.
        if (!$parent) {
            throw new Exception('Impossible de supprimer un commentaire parent');
        }

        //Cette ligne enlève le commentaire de réponse de la base de données à l'aide de l'objet EntityManagerInterface.
        $parent->removeReply($reply);


        $manager->remove($reply);
        $manager->flush();

        return $this->redirectToRoute('blog');
    }

    /**
     * Cette fonction permet de supprimer un commentaire.
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("admin/comment/{id}/delete", name="delete_comment")
     */
    public function deleteComment(Comment $comment, EntityManagerInterface $manager)
    {
        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('blog');
    }
}
