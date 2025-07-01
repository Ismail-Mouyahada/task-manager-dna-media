<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre de la tâche',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée de la tâche...',
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => Task::getStatusChoices(),
                'attr' => ['class' => 'form-select']
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => Task::getPriorityChoices(),
                'attr' => ['class' => 'form-select']
            ])
            ->add('dueDate', DateTimeType::class, [
                'label' => 'Date d\'échéance',
                'required' => false,
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'form-control datetimepicker',
                    'autocomplete' => 'off'
                ],
                'format' => 'dd/MM/yyyy HH:mm',
            ])
            ->add('assignedTo', EntityType::class, [
                'label' => 'Assigner à',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('u')
                        ->orderBy('u.firstName', 'ASC')
                        ->addOrderBy('u.lastName', 'ASC');
                },
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Non assigné',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
