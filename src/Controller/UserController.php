<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Service\EmailService;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @Route("/user")
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    // CRUD
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/add", name="user_add", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addUser (Request $request, EmailService $mailService, UserPasswordEncoderInterface $encoder): Response
    {
        $user     = new User();
        $form     = $this->createForm(UserType::class, $user);
        $datas    = $request->request->all();
        $em       = $this->getDoctrine()->getManager();
        // Génération du mot de passe => à automatiser (externaliser)
        $encoded  = $encoder->encodePassword($user, $datas['password']);
        $file     = $request->files->get('logo');
        
        // On catch l'erreur si il y'en a une
        try {
            $form->submit($datas);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'ça ne marche pas']);
        }

        // Si le formulaire et submit et valide tu me l'envoi en base de donnée
        if ($form->isSubmitted() && $form->isValid())
        {
            // Envisager de déporter l'assignation du rôle dans un listener Doctrine
            // On sépare donc la logique de rôle utilisateur de la création qui se trouve dans le contrôleur
            // Et si on a une création d'utilisateur à un autre endroit plus tard,
            // notre logique reste centralisée dans un listener externe
            // Veiller à respecter aussi la création d'administrateur (dans la commande Symfony, app:create-user), il ne faut pas qu'ils rentrent en conflit ou s'écrasent
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($encoded);
            if($file != null)
            {
                $user->setLogo($file);
            }

            $em->persist($user);
            $em->flush();

            // Idée d'évolution : transformer cette instruction en un événement personnalisé
            // Type : USER_CREATED
            // Ensuite, un listener branché sur cet événement pourrait envoyer automatiquement un mail
            // Et on pourrait brancher autant de listener qu'on voudrait à la création d'un utilisateur
            // Suivant l'évolution des besoins de l'application
            $this->sendInscriptionConfirmation($user, $mailService);
        }

        // Changer la réponse => "OK" ou autre message que le client pourrait récupérer et afficher
        // à l'utilisateur
        // Pour renvoyer du JSON, voir également : https://symfony.com/doc/current/controller.html#returning-json-response
        $data = $this->get('serializer')->serialize($user, 'json');

        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/show", name="user_show", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getConnectedUser(UploaderHelper $helper)
    {
        $user = $this->getUser();

        $user = [
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'telephone' => $user->getTelephone(),
            'entreprise' => $user->getNomEntreprise(),
            'adresse' => $user->getAdresse(),
            'code_postal' => $user->getCodePostal(),
            'ville' => $user->getVille(),
            'logo' => $helper->asset($user, 'logo'),
            'password' => $user->getPassword(),
            'social' => $user->getSocial(),
            'site_web' => $user->getSiteWeb()
        ];

        $data = $this->get('serializer')->serialize($user, 'json');

        return new JsonResponse($data, 200, [], true);
    }


    /**
     * @Route("/delete", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(Request $request, UserRepository $userRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $content = $request->getContent();

        $myData = json_decode($content, true);
        $id = $myData['id'];
        $user = $userRepository->findOneBy(['id' => $id]);


        $entityManager->remove($user);
        $entityManager->flush();


        $data = $this->get('serializer')->serialize('user deleted', 'json');

        return new JsonResponse($data, 200, [], true);
    }


    /**
     * @Route("/update", name="user_update", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws Error
     */
    public function updateUser(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $datas = $request->request->all();
        $user = $this->getUser();
        $file = $request->files->get('logo');

        $entityManager = $this->getDoctrine()->getManager();

        if ($user == null) {
            throw new Error('update is refused');
        }

        $form = $this->createForm(UserType::class, $user);

        try {
            $form->submit($datas);
        } catch (\Exception $e) {
            throw new Error('error');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $encoded  = $encoder->encodePassword($user, $datas['password']);

            $user = $form->getData();

            $user->setPassword($encoded);

            if($file != null)
            {
                $user->setLogo($file);
            }

            $entityManager->flush();
        }

        $data = $this->get('serializer')->serialize($user, 'json');

        return new JsonResponse($data, 200, [], true);

    }

    protected function sendInscriptionConfirmation(User $data, EmailService $emailService)
    {
        $body = $this->renderView('EmailTemplate/inscription.html.twig', [
            'prenom' => $data->getPrenom()
        ]);

        $userMailData =
            [
                "from" => "hoc2019@ld-web.net", // Envisager de sortir cette valeur dans une variable d'environnement
                "to" => $data->getEmail(),
                "subject" => "Bienvenue sur KeepMe !",
                "body" => $body,
            ];

        return new Response($emailService->sendEmail($userMailData));
    }
}
