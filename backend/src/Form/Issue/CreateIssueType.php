<?php

namespace App\Form\Issue;

use App\Entity\Issue\IssueType;
use App\Repository\Issue\IssueTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateIssueType extends AbstractType
{

    public function __construct(
        private readonly IssueTypeRepository $issueTypeRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('type', EntityType::class, [
                'class' => IssueType::class,
                'choices' => $this->getIssueTypes(),
                'choice_label' => 'label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateIssueForm::class
        ]);
    }

    /**
     * @return IssueType[]
     */
    private function getIssueTypes(): array
    {
        return $this->issueTypeRepository->fetchCreateTypes();
    }
}
