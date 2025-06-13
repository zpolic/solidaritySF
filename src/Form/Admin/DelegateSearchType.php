<?php

namespace App\Form\Admin;

use App\Entity\City;
use App\Entity\School;
use App\Entity\UserDelegateSchool;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DelegateSearchType extends AbstractType
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
            ->add('school', EntityType::class, [
                'class' => School::class,
                'required' => false,
                'placeholder' => '',
                'label' => 'Škola',
                'choice_value' => 'id',
                'choice_label' => function (School $school): string {
                    return $school->getName().' ('.$school->getCity()->getName().')';
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->innerJoin(UserDelegateSchool::class, 'uds', 'WITH', 'uds.school = s')
                        ->groupBy('s.id')
                    ->orderBy('s.name', 'ASC');
                },
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'required' => false,
                'placeholder' => '',
                'label' => 'Grad',
                'choice_value' => 'id',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->innerJoin(School::class, 's', 'WITH', 's.city = c')
                        ->innerJoin(UserDelegateSchool::class, 'uds', 'WITH', 'uds.school = s')
                        ->groupBy('c.id')
                        ->orderBy('c.name', 'ASC');
                },
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
