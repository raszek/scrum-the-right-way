<?php

namespace App\Form\Site;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ForgotPasswordForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('forgot_password_form', new ForgotPasswordFormData());

        $form->addField(new FormField(
            name: 'email',
            validators: [
                $v->notBlank(),
                $v->email(),
            ],
            widget: $this->formWidgetFactory->siteTextField('email'),
            label: 'Email'
        ));

        return $form;
    }
}
