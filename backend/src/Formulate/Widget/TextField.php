<?php

namespace App\Formulate\Widget;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormWidget;
use Twig\Environment;

readonly class TextField implements FormWidget
{

    public function __construct(
        private Environment $twig,
        private string $type = 'text',
        private string $template = 'formulate/widget/text_input.html.twig'
    ) {
    }

    public function render(FormField $formField, Form $form): string
    {
        return $this->twig->render($this->template, [
            'id' => $form->generateFieldId($formField),
            'name' => $form->generateFieldName($formField),
            'type' => $this->type,
            'label' => $formField->label ?? $formField->name,
            'error' => $formField->error,
            'value' => $formField->value()
        ]);
    }
}
