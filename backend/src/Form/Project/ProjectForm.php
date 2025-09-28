<?php

namespace App\Form\Project;

use App\Entity\Project\Project;
use App\Enum\Project\ProjectTypeEnum;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;
use App\Formulate\Widget\FormWidgetFactory;

readonly class ProjectForm
{

    public function __construct(
        private ValidatorFactory $validatorFactory,
        private FormWidgetFactory $formWidgetFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form('project_form', new ProjectFormData());

        $form->addField(
            new FormField(
                name: 'name',
                validators: [
                    $v->notBlank(),
                ],
                widget: $this->formWidgetFactory->textField(
                    attributes: [
                        'placeholder' => 'Title',
                        'class' => 'form-control-lg',
                        'data-project-code-target' => 'reader',
                        'data-action' => 'project-code#rewriteCode'
                    ],
                ),
                label: ''
            )
        );

        $form->addField(
            new FormField(
                name: 'code',
                validators: [
                    $v->notBlank(),
                    $v->regex(Project::CODE_REGEX)
                ],
                widget: $this->formWidgetFactory->textField(
                    attributes: [
                        'placeholder' => 'Code',
                        'class' => 'form-control-lg',
                        'maxlength' => 3,
                        'data-project-code-target' => 'writer',
                        'data-action' => 'project-code#upperCase'
                    ]
                ),
                label: ''
            )
        );

        $form->addField(
            new FormField(
                name: 'type',
                validators: [
                    $v->notBlank(),
                    $v->choice(array_keys($this->projectTypeOptions()))
                ],
                widget: $this->formWidgetFactory->radioField(
                    options: $this->projectTypeOptions(),
                    template: 'project/project_radio_input.html.twig'
                ),
                label: 'Project type'
            )
        );

        return $form;
    }

    private function projectTypeOptions(): array
    {
        return [
            ProjectTypeEnum::Scrum->key() => ProjectTypeEnum::Scrum->label()
        ];
    }

}
