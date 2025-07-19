<?php

namespace App\Form\User;

use App\Entity\User\User;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;
use App\Repository\User\UserRepository;
use Closure;

readonly class UserForm
{

    public function __construct(
        private FormWidgetFactory $widgetFactory,
        private ValidatorFactory $validator,
        private UserRepository $userRepository,
    ) {
    }

    public function create(?User $user = null): Form
    {
        $form = new Form('user_form', $this->getData($user));

        $form->addField(new FormField(
            name: 'email',
            validators: [
                $this->validator->notBlank(),
                $this->validator->email(),
                $this->validator->callback($this->uniqueEmail($user))
            ],
            widget: $this->widgetFactory->textField(),
            label: 'Email'
        ));

        $form->addField(new FormField(
            name: 'firstName',
            validators: [
                $this->validator->notBlank(),
            ],
            widget: $this->widgetFactory->textField(),
            label: 'First name'
        ));

        $form->addField(new FormField(
            name: 'lastName',
            validators: [
                $this->validator->notBlank(),
            ],
            widget: $this->widgetFactory->textField(),
            label: 'Last name'
        ));

        return $form;
    }

    private function uniqueEmail(?User $user): Closure
    {
        if (!$user) {
            return $this->emailTaken(...);
        }

        return function (FormField $field) use ($user) {
            if ($field->value() === $user->getEmail()) {
                return null;
            }

            return $this->emailTaken($field);
        };
    }

    private function emailTaken(FormField $field): ?FormFieldError
    {
        $foundUser = $this->userRepository->findOneBy([
            'email' => $field->value()
        ]);

        if ($foundUser) {
            return new FormFieldError(sprintf('%s is already in use', $field->label()));
        }

        return null;
    }

    private function getData(?User $user): UserFormData
    {
        if (!$user) {
            return new UserFormData();
        }

        return new UserFormData(
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
        );
    }

}
