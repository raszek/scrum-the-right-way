<?php

namespace App\Form\Sprint;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;

readonly class UpdateEstimatedEndDateForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form(
            data: new UpdateEstimatedEndDateFormData(),
        );

        $form->addField(new FormField('value', [
            $v->notBlank(),
            $v->date(),
        ]));

        return $form;
    }

}
