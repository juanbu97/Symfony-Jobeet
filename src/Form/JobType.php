<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class JobType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(Job::TYPES, Job::TYPES),
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 255
                    ])
                ]
            ])
            ->add('company', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 255
                    ])
                ]
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'constraints'=> [
                    new Image()
                ]
            ])
            ->add('url', UrlType::class)
            ->add('position', TextType::class)
            ->add('location', TextType::class)
            ->add('description', TextareaType::class)
            ->add('howToApply', TextType::class)
            ->add('public', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false
                ], 'label' => 'Public?'
            ])
            ->add('activated', TextType::class)
            ->add('email', EmailType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name'
            ]);
            
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Job::class]);
    }
}
