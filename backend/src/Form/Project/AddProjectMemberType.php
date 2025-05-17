<?php

namespace App\Form\Project;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddProjectMemberType extends AbstractType
{

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var AddProjectMemberForm $data
         */
        $data = $builder->getData();

        $builder->add('email', TextType::class, [
            'autocomplete' => true,
            'autocomplete_url' => $this->urlGenerator->generate('app_project_non_members', [
                'id' => $data->project->getId()
            ]),
            'tom_select_options' => [
                'maxItems' => 1
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddProjectMemberForm::class,
        ]);
    }
}
