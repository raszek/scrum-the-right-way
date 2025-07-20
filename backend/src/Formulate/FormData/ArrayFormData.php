<?php

namespace App\Formulate\FormData;

use App\Formulate\Form;
use App\Formulate\FormField;

readonly class ArrayFormData implements FormDataInterface
{

    public function __construct(
        private Form $form,
        private ?array $data = null,
    ) {
    }

    public function get(): array
    {
        $data = $this->data ?? [];

        foreach ($this->form->fields() as $field) {
            $data[$field->name] = $field->value();
        }

        return $data;
    }

    public function loadField(FormField $field): void
    {
        $loadedFields = $this->data ?? [];

        if (!isset($loadedFields[$field->name])) {
            return;
        }

        $field->load($loadedFields[$field->name]);
    }
}
