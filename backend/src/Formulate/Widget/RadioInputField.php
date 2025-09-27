<?php

namespace App\Formulate\Widget;

use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormWidget;
use Twig\Environment;

readonly class RadioInputField implements FormWidget
{

    public function __construct(
        private Environment $twig,
        private array $options,
        private string $template = 'formulate/widget/radio_input.html.twig',
        private array $attributes = [],
    ) {
    }

    public function render(FormField $formField, Form $form): string
    {
        return $this->twig->render($this->template, [
            'options' => $this->createOptions($formField, $form),
            'error' => $formField->error,
        ]);
    }

    private function createOptions(FormField $formField, Form $form): array
    {
        $options = [];
        foreach ($this->options as $value => $label) {
            $options[] = [
                'id' => $form->generateFieldId($formField).'_'.$value,
                'name' => $form->generateFieldName($formField),
                'label' => $label,
                'value' => $value,
                'attributes' => $this->attributes,
                'class' => $this->attributes['class'] ?? '',
            ];
        }

        return $options;
    }
}
