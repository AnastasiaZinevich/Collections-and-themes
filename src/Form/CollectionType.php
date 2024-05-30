<?php
namespace App\Form;
use Symfony\Component\Form\FormError;



use App\Entity\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as FormCollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Form\StringToArrayTransformer;
use Doctrine\ORM\EntityManagerInterface;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a name.']),
            ],
        ])
        ->add('description', TextareaType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Description cannot be blank.']),
            ],
        ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Books' => 'Books',
                    'Signs' => 'Signs',
                    'Silverware' => 'Silverware',
                    'Other' => 'Other',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Upload Image',
                'mapped' => false,
                'required' => false,
            ])
            ->add('customFields', FormCollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collection::class,
        ]);
    }
}
