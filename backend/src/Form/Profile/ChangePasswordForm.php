<?php

namespace App\Form\Profile;


use App\Entity\User\User;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\Validator\Callback;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ChangePasswordForm
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }


    public function create(User $user): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('change_password_form', new ChangePasswordFormData);

        $form->addField(new FormField(
            name: 'currentPassword',
            validators: [
                $v->notBlank(),
                $this->currentPasswordValidator($user),
            ],
            widget: $this->formWidgetFactory->passwordField(),
            label: 'Current password'
        ));

        $form->addField(new FormField(
            name: 'newPassword',
            validators: [
                $v->notBlank(),
                $v->repeat('repeatPassword')
            ],
            widget: $this->formWidgetFactory->passwordField(),
            label: 'New password'
        ));

        $form->addField(new FormField(
            name: 'repeatPassword',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->passwordField(),
            label: 'Repeat password'
        ));

        return $form;
    }

    private function currentPasswordValidator(User $user): Callback
    {
        return new Callback(function (FormField $field) use ($user) {
            $isValid = $this->userPasswordHasher->isPasswordValid($user, $field->value());

            if (!$isValid) {
                return new FormFieldError('Current password is incorrect');
            }

            return null;
        });
    }
}
