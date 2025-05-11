<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonorSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'Ime',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'Prezime',
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('isMonthly', ChoiceType::class, [
                'required' => false,
                'label' => 'Mesečna podrška',
                'choices' => [
                    'Da' => true,
                    'Ne' => false,
                ],
            ])
            ->add('how', ChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'label' => 'Kako ste saznali?',
                'choices' => [
                    '-' => -1,
                    'Čuo/la sam na televiziji' => 1,
                    'Saznao/la preko društvenih mreža' => 2,
                    'Preko člana porodice/prijatelja' => 3,
                    'Preko news portala / foruma / Reditta' => 4,
                    'Preko škole, fakulteta…' => 5,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="ti ti-search text-2xl"></i> Pretraži',
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
