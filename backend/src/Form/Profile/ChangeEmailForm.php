<?php

namespace App\Form\Profile;


use App\Entity\User\User;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ChangeEmailForm
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }

    public function create(User $user): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('change_email_form', ChangeEmailFormData::fromUser($user));

        $form->addField(new FormField(
            name: 'email',
            validators: [
                $v->notBlank(),
                $v->email(),
            ],
            widget: $this->formWidgetFactory->emailField(),
            label: 'Email'
        ));

        return $form;
    }
}
