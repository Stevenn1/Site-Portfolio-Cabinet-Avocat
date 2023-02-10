<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * Cette fonction permet d'afficher les messages envoyés par les clients,
     * ils sont affichés avec une limite de 6 par pages.
     * 
     * @Route("/admin/contact", name="admin_contact", methods={"GET"})
     */
    public function DisplayMessages(ContactRepository $contactRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupère tous les messages triés par ordre décroissant de date de création
        $contacts = $contactRepository->FindByDesc();

        // Pagination des messages
        $contacts = $paginator->paginate(
            $contacts, // Collection de messages à paginer
            $request->query->getInt('page', 1), // Numéro de page actuel
            6 // Nombre de messages à afficher par page
        );

        return $this->render("admin/contact/contact.html.twig", [
            'contacts' => $contacts,
        ]);
    }

    /**
     * Cette fonction permet d'afficher un message.
     * 
     * @Route("/admin/contact/{id}", name="admin_contact_show", methods={"GET", "POST"})
     */
    public function show(Contact $contact): Response
    {
        return $this->render('admin/contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    /**
     * Cette fonction permet de supprimer un message reçu par un client.
     * 
     * @Route("admin/contact/{id}/supprimer", name="admin_contact_remove", methods={"GET"})
     */
    public function removeMessage(int $id, ContactRepository $contactRepository, EntityManagerInterface $manager): Response
    {
        $contact = $contactRepository->find($id);

        $manager->remove($contact);
        $manager->flush();

        $this->addFlash('message', 'Votre message reçu a bien été supprimé');
        return $this->redirectToRoute('admin_contact');
    }
}
