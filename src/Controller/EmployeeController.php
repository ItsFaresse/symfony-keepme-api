<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use PhpParser\Error;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;


/**
 * @Route("/employee")
 */
class EmployeeController extends AbstractController
{
    private $userRepository;

    public function  __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/add", name="employee_add", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function add (Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $content = $request->getContent();

        $data = json_decode($content, true);
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            throw new Error('No User found');
        }


        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);

        try{
            $form->submit($data);
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }


        if ($form->isSubmitted() && $form->isValid()) {

            $employee = $form->getData();
            $employee->setUser($user);
            $em->persist($employee);
            $em->flush();

        }
        $data = $this->get('serializer')->serialize($employee, 'json');

        return new JsonResponse($data, 200, [], true);
        
    }

    /**
     * @Route("/show", name="employee_show", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmployee(Request $request, EmployeeRepository $employeeRepository, UploaderHelper $helper)
    {
        $content = $request->getContent();

        $data = json_decode($content, true);
        $employee = $employeeRepository->findOneBy($data);

        $connectedUser = $this->getUser();
        $userOfEmployee = $employee->getUser();

        if($connectedUser !== $userOfEmployee)
        {
            throw new Error('operation not allowed');
        }

        // Possibilité de factoriser ce code dans l'entité Employee directement
        // On pourrait en bénéficier partout
        $employee = [
            'nom' => $employee->getNom(),
            'prenom' => $employee->getPrenom(),
            'email' => $employee->getEmail(),
            'poste' => $employee->getPoste(),
            'telephone' => $employee->getTelephone(),
            'slug' => $employee->getSlug(),
            'entreprise' => $employee->getUser()->getNomEntreprise(),
            'logo' => $helper->asset($userOfEmployee, 'logo')
        ];

        $data = $this->get('serializer')->serialize($employee, 'json');

        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/list", name="employee_list", methods={"GET"})
     * @return JsonResponse
     */
    public function getEmployeesList (UserRepository $userRepository, UploaderHelper $helper)
    {
        $employeeList = [];
        // getUser retourne déjà une entité
        $connectedUser = $this->getUser();
        $user = $userRepository->find($connectedUser);
        $employees = $user->getEmployees();

        foreach ($employees as $employee)
        {
            // Factoriser dans entité Employee
            $employee = [
                'nom' => $employee->getNom(),
                'prenom' => $employee->getPrenom(),
                'email' => $employee->getEmail(),
                'poste' => $employee->getPoste(),
                'telephone' => $employee->getTelephone(),
                'slug' => $employee->getSlug(),
                'entreprise' => $employee->getUser()->getNomEntreprise(),
                'logo' => $helper->asset($employee, 'logo')
            ];

            array_push($employeeList, $employee);
        }

        $data = $this->get('serializer')->serialize($employeeList, 'json');

        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/delete", name="employee_delete", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteEmployee (Request $request, EmployeeRepository $employeeRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $content = $request->getContent();

        $myData = json_decode($content, true);
        $id = $myData['id'];
        // Utiliser find(id)
        $employee = $employeeRepository->findOneBy(['id' => $id]);

        $entityManager->remove($employee);
        $entityManager->flush();


        $data = $this->get('serializer')->serialize('employee deleted', 'json');

        return new JsonResponse($data, 200, [], true);
    }


    /**
     * @Route("/update/{employeeId}", name="employee_update", methods={"PUT"}, requirements={"employeeId"="\d+"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateEmployee (Request $request, EmployeeRepository $employeeRepository, $employeeId)
    {
        $content = $request->getContent();
        $entityManager = $this->getDoctrine()->getManager();
        $myData = json_decode($content, true);

        $employee = $employeeRepository->find($employeeId);
        $user = $employee->getUser();
        $currentUser =$this->getUser();

        if($user !== $currentUser)
        {
            throw new Error('update is refused');
        }

        $form = $this->createForm(EmployeeType::class, $employee);

        try{
            $form->submit($myData);
        }catch (\Exception $e){
            throw new Error('error');
        }


        if ($form->isSubmitted() && $form->isValid())
        {
            $employee = $form->getData();
            $entityManager->flush();
        }

        $data = $this->get('serializer')->serialize($employee, 'json');

        return new JsonResponse($data, 200, [], true);

    }

    /**
     * @Route("/sendcard", name="employee_send_card", methods={"POST"})
     * @param Request $request
     * @param EmailService $emailService
     * @param EmployeeRepository $employeeRepository
     * @return JsonResponse|Response
     */
    public function sendCardByMail(Request $request, EmailService $emailService, EmployeeRepository $employeeRepository)
    {
        $formValues = json_decode($request->getContent(), true);
        $slug = $formValues['slug'];
        $employee = $employeeRepository->findOneBySlug($slug);
        /* $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);
        $form->submit($formValues);*/

        if ($employee != null)
        {
            $body = $this->renderView('EmailTemplate/carteVisite.html.twig', [
                'employeePrenom' => $employee->getPrenom(),
                'userPrenom' => $employee->getUser()->getPrenom(),
                'userNom' => $employee->getUser()->getNom(),
                // Attention aux URL en dur dans l'application
                'slug' => "http://localhost:3000/card/".$employee->getSlug()
            ]);

            $userMailData =
                [
                    "from" => "hoc2019@ld-web.net", // <-- externaliser dans variable d'environnement
                    "to" => $employee->getEmail(),
                    "subject" => "KeepMe : votre carte de visite numérique",
                    "body" => $body,
                ];
            return new Response($emailService->sendEmail($userMailData));
        }
        else
        {
            return new Response("Cet(te) employé(e) n'existe pas");
        }
    }
}
