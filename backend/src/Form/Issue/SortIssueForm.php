<?php

namespace App\Form\Issue;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;

readonly class SortIssueForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form(
            data: new SortIssueFormData()
        );

        $form->addField(new FormField(
            name: 'position',
            validators: [
                $v->greaterThan(0),
                $v->notBlank(),
                $v->integer()
            ]
        ));

        return $form;
    }

}
