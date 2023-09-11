<?php

namespace App\Form;

use App\Entity\GiftList;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints as Assert;

class GiftListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le titre ne peut pas être vide.",
                    ]),
                ],
            ])

            ->add('description', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "La description ne peut pas être vide.",
                    ]),
                ],
            ])

            ->add('theme', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le thème ne peut pas être vide.",
                    ]),
                ],
            ])

            ->add('imageFile', VichImageType::class, [
                'label' => 'Image du cadeau  (JPEG, PNG, GIF,SVG)',
                'required' => false,
            ])
            ->add('privacy')
            ->add('password')
            ->add('dateOuverture', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['min' => (new \DateTime())->format('d-m-Y')],
                'data' => new \DateTime(),
            ])
            ->add('dateFermeture', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['min' => (new \DateTime())->format('d-m-Y')],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GiftList::class,
        ]);
    }
}
