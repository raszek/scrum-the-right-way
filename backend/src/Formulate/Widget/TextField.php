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
        private string $type = 'text'
    ) {
    }

    public function render(FormField $formField, Form $form): string
    {
        return $this->twig->render('formulate/widget/text_input.html.twig', [
            'id' => $form->generateFieldId($formField),
            'name' => $form->generateFieldName($formField),
            'type' => $this->type,
            'label' => $formField->label ?? $formField->name,
            'error' => $formField->error,
            'value' => $formField->value()
        ]);
    }
}
