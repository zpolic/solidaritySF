<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentRawPassword', PasswordType::class, [
                'label' => 'Trenutna lozinka',
            ])
            ->add('rawPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Lozinke se ne poklapaju',
                'first_options' => [
                    'label' => 'Nova lozinka',
                ],
                'second_options' => [
                    'label' => 'Ponovite novu lozinku',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sačuvaj',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['currentRawPassword', 'rawPassword'],
        ]);
    }
}
