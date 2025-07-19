<?php

namespace App\Formulate\FormData;

use App\Formulate\Form;

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

    public function load(): void
    {
        $loadedFields = $this->data ?? [];

        foreach ($this->form->fields() as $field) {
            if (!isset($loadedFields[$field->name])) {
                continue;
            }

            $field->load($loadedFields[$field->name]);
        }
    }
}
