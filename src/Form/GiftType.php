<?php

namespace App\Form;

use App\Entity\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;


class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prix')
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image du cadeau  (JPEG, PNG, GIF)',
                'required' => false,
            ])



            ->add('lien_achat')
            ->add('status')
            ->add("name")
            ->add('email', null, [
                'constraints' => [
                    new Assert\Email([
                        'message' => "L'email '{{ value }}' n'est pas valide.",
                    ]),
                ],
            ]);
            /* ->add('GiftList'); */;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
        ]);
    }
}
