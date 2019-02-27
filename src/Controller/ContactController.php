<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailService;

class ContactController extends AbstractController
{
    /**
     * @param Request $request
     * @Route("/contact/mail", name="contact_mail", methods={"POST"})
     */
    public function getContactFormResponse(Request $request, EmailService $emailService)
    {
        $formValues = json_decode($request->getContent(), true);
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request); // Inutile ici
        $form->submit($formValues);

        if ($form->isSubmitted() && $form->isValid())
        {
            $contact = $form->getData();
            $this->sendContactNotificationToUser($contact, $emailService);
            $this->sendContactNotificationToAdmin($contact, $emailService);
            return new Response('200');
        }
        else
        {
            return new Response('Error');
        }
    }

    protected function sendContactNotificationToUser(Contact $data, EmailService $emailService)
    {
        $body = $this->renderView('EmailTemplate/contact.html.twig', [
            'prenom' => $data->getNom()
        ]);

        $userMailData =
            [
                "from" => "hoc2019@ld-web.net",
                "to" => $data->getEmail(),
                "subject" => "Merci de nous avoir contacté !",
                "body" => $body,
            ];

        return new Response($emailService->sendEmail($userMailData));
    }

    protected function sendContactNotificationToAdmin(Contact $data, EmailService $emailService)
    {
        $body = $this->renderView('EmailTemplate/adminContact.html.twig', [
            'nom' => $data->getNom(),
            'prenom' => $data->getPrenom(),
            'entreprise' => $data->getEntreprise(),
            'email' => $data->getEmail(),
            'objet' => $data->getObjet(),
            'message' => $data->getMessage()
        ]);

        $adminMailData =
            [
                "from" => $data->getEmail(),
                "to" => "hoc2019@ld-web.net",
                "subject" => "Nouveau contact : " . $data->getObjet(),
                "body" => $body,
            ];

        return new Response($emailService->sendEmail($adminMailData));
    }

}
