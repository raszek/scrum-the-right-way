<?php

namespace App\Form\Event;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Event\FullEventList;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchEventType extends AbstractType
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FullEventList $eventSelector
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var SearchEventForm $data
         */
        $data = $builder->getData();

        $builder
            ->setMethod('GET')
            ->add('name', ChoiceType::class, [
                'choices' => $this->eventSelector->selections(),
                'required' => false,
                'autocomplete' => true,
            ])
            ->add('createdBy', EntityType::class, [
                'class' => User::class,
                'choices' => $this->projectMembers($data->project),
                'autocomplete' => true,
                'choice_label' => fn(User $user) => $user->getDomainName(),
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchEventForm::class
        ]);
    }

    /**
     * @return User[]
     */
    private function projectMembers(Project $project): array
    {
        return $this->userRepository->projectUsers($project);
    }
}
