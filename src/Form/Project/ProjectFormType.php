<?php

namespace App\Form\Project;

use App\Entity\Project\ProjectType;
use App\Repository\Project\ProjectTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectFormType extends AbstractType
{

    public function __construct(
        private readonly ProjectTypeRepository $projectTypeRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('code')
            ->add('type', EntityType::class, [
                'class' => ProjectType::class,
                'choice_label' => 'label',
                'choices' => $this->getTypes()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectForm::class,
        ]);
    }

    private function getTypes(): array
    {
        return [
            $this->projectTypeRepository->scrumType()
        ];
    }
}
