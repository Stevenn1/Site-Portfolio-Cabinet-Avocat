<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\SubscriptionType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class UserController extends AbstractController
{
    /**
     * Cette fonction permet d'effectuer l'inscription sur le site.
     * 
     * @Route("/inscription", name="app_front_user_subscription")
     */
    public function subscription(Request $request, UserPasswordHasherInterface $crypter, EntityManagerInterface $manager): Response
    {
        // Création d'un formulaire à partir du type "SubscriptionType"
        $form = $this->createForm(SubscriptionType::class);

        // Traitement des données du formulaire
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $user = $form->getData();

            // Chiffrement du mot de passe
            $user->setPassword($crypter->hashPassword(
                $user,
                $user->getPassword(),
            ));

            // Persistence et enregistrement des données en base de données
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('message', 'Votre compte a bien été crée');

            return $this->redirectToRoute('app_login');
        }

        // Affiche la vue de la page d'inscription avec le formulaire
        return $this->render('front/user/subscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Cette fonction gère la connexion de l'utilisateur.
     * 
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si un utilisateur est connecté. Si oui, il redirige vers la page "home"
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Récupérer l'erreur de connexion s'il en existe une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Cette fonction gère la déconnexion de l'utilisateur.
     * 
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Cette fonction permet la suppression d'un utilisateur.
     * 
     * @Route("/delete/{id}", name="app_user_delete", methods={"POST", "GET"})
     *
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @param UserRepository $userRepository
     *
     * @return Response
     */
    public function delete(Request $request, User $user, EntityManagerInterface $manager, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Efface le jeton d'authentification actuel
        $this->container->get('security.token_storage')->setToken(null);

        // Supprime l'utilisateur de la base de données
        $manager->remove($user);
        $manager->flush();

        // Envoie un email de confirmation de suppression
        $email = (new Email())
            ->from('localhost@mail.fr')
            ->to('stevenmichel6@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Désinscription')
            ->text('Suite à votre demande,nous vous confirmons la suppression de votre profil.<br> Cordialement,Maitre " ADMINISTRATEUR " 13 Boulevard Jean Rose 
            77100 MEAUX ')
            ->html('Suite à votre demande,<br> nous vous confirmons la suppression de votre profil.<br> Cordialement,<br><br> <strong>Maitre " ADMINISTRATEUR " <br> 13 Boulevard Jean Rose <br>
            77100 MEAUX</strong>');

        $mailer->send($email);

        // Ajoute un message flash de confirmation de suppression
        $this->addFlash('message', 'Votre compte utilisateur a bien été supprimé !');

        // Redirige vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
}
