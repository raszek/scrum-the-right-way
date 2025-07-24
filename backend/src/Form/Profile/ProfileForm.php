<?php

namespace App\Form\Profile;


use App\Entity\User\User;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ProfileForm
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }


    public function create(User $user): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('profile_form', ProfileFormData::fromUser($user));

        $form->addField(new FormField(
            name: 'firstName',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->textField(),
            label: 'First name'
        ));

        $form->addField(new FormField(
            name: 'lastName',
            validators: [
                $v->notBlank(),
            ],
            widget: $this->formWidgetFactory->textField(),
            label: 'Last name'
        ));

        return $form;
    }

}
