<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * Cette fonction permet à l'utilisateur de pouvoir contacter l'avocat,
     * elle permet également à l'administrateur de pouvoir traité la demande.
     * 
     * @Route("/contact", name="contact", methods={"GET", "POST"})
     * @Route("admin/contact/{id}/checked", name="admin_contact_checked", methods={"GET", "POST"})
     */
    public function formContact(Contact $contact = null, Request $request, EntityManagerInterface $manager): Response
    {
        // Vérification s'il s'agit d'un nouveau message de contact ou non
        if (!$contact) {
            $contact = new Contact();
        }

        // Création du formulaire en utilisant le type de formulaire ContactType
        $form = $this->createForm(ContactType::class, $contact);

        // Traitement des données du formulaire
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et s'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Si c'est un nouveau message de contact
            if (!$contact->getId()) {
                // Définir la date de création
                $contact->setCreatedAt(new \DateTime());

                $manager->persist($contact);
                $manager->flush();

                $this->addFlash('message', 'le message a bien été transmis');

                return $this->redirectToroute('contact');
            }

            // Mise à jour du message en base de données en cas de traitement du message
            $manager->persist($contact);
            $manager->flush();

            $this->addFlash('message', 'le message a bien été traité');

            return $this->redirectToroute('admin_contact_show', ['id' => $contact->getId()]);
        }

        // Affichage du formulaire de contact
        return $this->render('front/contact.html.twig', [
            'form' => $form->createView(),
            'checkMode' => $contact->getId() !== null
        ]);
    }
}
