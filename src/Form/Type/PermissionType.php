<?php

namespace Pantheon\UserBundle\Form\Type;

use Pantheon\UserBundle\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionType extends AbstractType
{

    public function __construct(
        // TODO: роли, у которых есть этот пермишн. И пользователи
    ) {
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}