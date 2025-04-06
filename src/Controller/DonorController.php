<?php

namespace App\Controller;

use App\Entity\UserDonor;
use App\Form\UserDonorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(name: 'donor_')]
class DonorController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/postani-donator', name: 'become')]
    public function edit(Request $request): Response
    {
        $userDonor = $this->getUser()->getUserDonor() ?? new UserDonor();
        $userDonor->setUser($this->getUser());

        $form = $this->createForm(UserDonorType::class, $userDonor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($userDonor);
            $this->entityManager->flush();

            return $this->redirectToRoute('donor_success');
        }

        return $this->render('donor/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/uspesna-registracija-donora', name: 'success')]
    public function messageSuccessSupport(): Response
    {
        return $this->render('donor/success.html.twig');
    }
}