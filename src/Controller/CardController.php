<?php
namespace App\Controller;

use App\Repository\EmployeeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @Route("/card")
 */
class CardController extends AbstractController
{
    /**
     * @Route("/{slug}", name="card_employee", methods={"GET"})
     * @return JsonResponse
     */
    public function getEmployeeForGeneratingCard($slug, EmployeeRepository $employeeRepository, UploaderHelper $helper)
    {
        $employee = $employeeRepository->findOneBy(['slug' => $slug]);

        $employee = [
            'nom' => $employee->getNom(),
            'prenom' => $employee->getPrenom(),
            'email' => $employee->getEmail(),
            'poste' => $employee->getPoste(),
            'telephone' => $employee->getTelephone(),
            'slug' => $slug,
            'entreprise' => $employee->getUser()->getNomEntreprise(),
            'logo' => $helper->asset($employee->getUser(), 'logo')
        ];

        $data = $this->get('serializer')->serialize($employee, 'json');

        return new JsonResponse($data, 200, [], true);
    }
}
