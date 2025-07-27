<?php

namespace App\Form\Site;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ResetPasswordForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }

    public function create(ResetPasswordFormData $formData): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('reset_password_form', $formData);

        $form->addField(new FormField(
            name: 'password',
            validators: [
                $v->notBlank(),
                $v->repeat('repeatPassword')
            ],
            widget: $this->formWidgetFactory->siteTextField('password'),
            label: 'Password',
        ));

        $form->addField(new FormField(
            name: 'repeatPassword',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->siteTextField('password'),
            label: 'Repeat password',
        ));

        $form->addField(new FormField(
            name: 'resetPasswordCode',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->hiddenField(),
        ));

        $form->addField(new FormField(
            name: 'email',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->hiddenField(),
        ));

        return $form;
    }
}
