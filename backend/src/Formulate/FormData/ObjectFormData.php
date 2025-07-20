<?php

namespace App\Formulate\FormData;

use App\Formulate\Form;
use App\Formulate\FormField;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

readonly class ObjectFormData implements FormDataInterface
{

    private PropertyAccessor $propertyAccessor;

    public function __construct(
        private Form $form,
        private object $data
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function get(): object
    {
        $data = $this->data;

        foreach ($this->form->fields() as $field) {
            $this->propertyAccessor->setValue($data, $field->name, $field->value());
        }

        return $data;
    }

    public function loadField(FormField $field): void
    {
        $field->load($this->propertyAccessor->getValue($this->data, $field->name));
    }
}
