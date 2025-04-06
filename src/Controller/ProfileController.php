<?php

namespace App\Controller;

use App\Form\ProfileChangePasswordType;
use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil', name: 'profile_')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'edit')]
    public function edit(Request $request): Response
    {
        $form = $this->createForm(ProfileEditType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Podaci su uspešno izmenjeni');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/izmena-sifre', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileChangePasswordType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $user->getRawPassword()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lozinka je uspešno promenjena');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}