<?php

namespace Pantheon\UserBundle\Form\Type;

use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Pantheon\UserBundle\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function __construct(
        RoleRepository $roleRepository
    ) {
        $this->roleRepository = $roleRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Логин',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Логин',
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'E-mail',
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mail',
                    'readonly' => 'readonly',
                ],
            ])

            ->add('name', TextType::class, [
                'label' => 'Имя',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Имя',
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Фамилия',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Фамилия',
                ],
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'Отчество',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Отчество',
                ],
            ])
            ->add('workplace', TextType::class, [
                'label' => 'Место работы',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Место работы',
                ],
            ])
            ->add('duty', TextType::class, [
                'label' => 'Должность',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Должность',
                ],
            ])
            ->add('birthdate', DateType::class, [
                'format' => 'dd.MM.yyyy',

                'label' => 'Дата рождения',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'ДД.ММ.ГГГГ',
                    'data-calendar' => true,
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Номер телефона',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Номер телефона',
                ],
            ])


            ->add('role', ChoiceType::class, [
                'label' => 'Роли',
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getName();
                },
                'choices' => $this->roleRepository->findAll(),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'data' => $options['data']->getRole()->getValues(),
                'attr' => [
                    'data-select2-width' => '100%',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}