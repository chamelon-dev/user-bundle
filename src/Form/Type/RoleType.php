<?php

namespace Pantheon\UserBundle\Form\Type;

use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{

    public function __construct(
        PermissionRepository $permissionRepository
    ) {
        $this->pemissionRepository = $permissionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Машинное имя',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Машинное имя',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Название на русском',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Название на русском',
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Описание',
                ],
            ])
            ->add('permissions', ChoiceType::class, [
                'label' => 'Пермишны',
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getName();
                },
                'choices' => $this->pemissionRepository->findAll(),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'data' => $options['data']->getPermissions()->getValues(),
                'attr' => [
                    'data-select2-width' => '100%',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}