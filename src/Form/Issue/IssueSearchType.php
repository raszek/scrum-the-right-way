<?php

namespace App\Form\Issue;

use App\Entity\Issue\IssueColumn;
use App\Entity\Issue\IssueType;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueSearchType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var IssueSearchForm $data
         */
        $data = $builder->getData();

        $builder
            ->setMethod('GET')
            ->add('title')
            ->add('number', IntegerType::class)
            ->add('column', EntityType::class, [
                'class' => IssueColumn::class,
                'choice_label' => 'label',
                'required' => false
            ])
            ->add('type', EntityType::class, [
                'class' => IssueType::class,
                'choice_label' => 'label',
                'required' => false
            ])
            ->add('createdBy', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->projectUsers($data->project),
                'choice_label' => 'fullName',
                'autocomplete' => true,
                'required' => false
            ])
            ->add('createdAfter', DateType::class)
            ->add('createdBefore', DateType::class)
            ->add('updatedAfter', DateType::class)
            ->add('updatedBefore', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IssueSearchForm::class
        ]);
    }
}
