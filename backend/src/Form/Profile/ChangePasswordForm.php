<?php

namespace App\Form\Profile;


use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ChangePasswordForm
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }


    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('change_password_form', new ChangePasswordFormData);

        $form->addField(new FormField(
            name: 'currentPassword',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->passwordField(),
            label: 'Current password'
        ));

        $form->addField(new FormField(
            name: 'newPassword',
            validators: [
                $v->notBlank(),
                $v->callback($this->validateSamePassword(...))
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

    private function validateSamePassword(FormField $field, Form $form): ?FormFieldError
    {
        $repeatPassword = $form->findField('repeatPassword')->value();

        if ($field->value() !== $repeatPassword) {
            return new FormFieldError('Passwords do not match');
        }

        return null;
    }
}
