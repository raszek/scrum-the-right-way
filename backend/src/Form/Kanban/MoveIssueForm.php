<?php

namespace App\Form\Kanban;

use App\Enum\Issue\IssueColumnEnum;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;

readonly class MoveIssueForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form(
            data: new MoveIssueFormData()
        );

        $form->addField(
            new FormField(
                name: 'position',
                validators: [
                    $v->notBlank(),
                    $v->integer(),
                    $v->greaterThan(0)
                ]
            )
        );

        $form->addField(
            new FormField(
                name: 'column',
                validators: [
                    $v->notBlank(),
                    $v->choice(IssueColumnEnum::kanbanColumnKeys())
                ]
            )
        );

        return $form;
    }

}
