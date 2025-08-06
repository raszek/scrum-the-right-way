<?php

namespace App\Formulate;

use App\Formulate\FormData\ArrayFormData;
use App\Formulate\FormData\FormDataInterface;
use App\Formulate\FormData\ObjectFormData;
use Symfony\Component\HttpFoundation\Request;
use Twig\Markup;

class Form
{

    /**
     * @var array<string, FormField>
     */
    private array $fields = [];

    private FormMethodEnum $method = FormMethodEnum::Post;

    private FormDataInterface $data;

    private bool $isSubmitted = false;

    public function __construct(
        private readonly ?string $name = null,
        mixed $data = null,
    ) {
        $this->loadData($data);
    }


    /**
     * @param FormField $formField
     * @return void
     */
    public function addField(FormField $formField): void
    {
        $this->fields[$formField->name] = $formField;
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function begin(): Markup
    {
        return $this->markup(sprintf('<form method="%s">', $this->method->lowercase()));
    }

    public function field(string $field): Markup
    {
        $field = $this->findField($field);

        if (!$field->widget) {
            throw new FieldDoesNotHaveWidgetException('Field cannot be rendered. Field does not have a widget.');
        }

        if ($this->isSubmitted === false) {
            $this->data->loadField($field);
        }

        return $this->markup($field->widget->render($field, $this));
    }

    public function end(): Markup
    {
        return $this->markup('</form>');
    }

    public function loadRequest(Request $request): bool
    {
        if ($request->getMethod() !== $this->method->value) {
            return false;
        }

        $submittedFields = $this->getSubmittedFields($request);
        if (empty($submittedFields)) {
            return false;
        }

        foreach ($submittedFields as $fieldKey => $value) {
            $this->fields[$fieldKey]->load($value);
        }

        $this->isSubmitted = true;
        return true;
    }

    public function hasErrors(): bool
    {
        return array_any($this->fields, fn($field) => $field->error);
    }

    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->fields as $field) {
            if ($field->error) {
                $errors[$field->name] = $field->error->message();
            }
        }

        return $errors;
    }

    public function validate(): bool
    {
        $isValid = true;
        foreach ($this->fields as $field) {
            if (!$field->validate($this)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function generateFieldName(FormField $field): string
    {
        if ($this->name === null) {
            return $field->name;
        }

        return sprintf('%s[%s]', $this->name, $field->name);
    }

    public function generateFieldId(FormField $field): string
    {
        if ($this->name === null) {
            return $field->name;
        }

        return sprintf('%s_%s', $this->name, $field->name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function getData(): mixed
    {
        return $this->data->get();
    }

    public function findField(string $field): FormField
    {
        if (!isset($this->fields[$field])) {
            throw new FieldDoesNotExistException(sprintf('Field "%s" does not exist.', $field));
        }

        return $this->fields[$field];
    }

    private function markup(string $content): Markup
    {
        return new Markup($content, 'UTF-8');
    }

    private function loadData(mixed $data): void
    {
        if (is_object($data)) {
            $this->data = new ObjectFormData($this, $data);
        } else if (is_array($data) || $data === null) {
            $this->data = new ArrayFormData($this, $data);
        } else {
            throw new InvalidDataTypeException('Data must be an object or an array or null.');
        }
    }

    private function getSubmittedFields(Request $request): array
    {
        $submittedData = $this->name === null ? $request->request->all() : $request->get($this->name);
        $submittedFiles = $this->name === null ? $request->files->all() : $request->files->get($this->name);

        $submittedFields = [];
        foreach ($this->fields as $field) {
            $submittedValue = $submittedData[$field->name] ?? $submittedFiles[$field->name] ?? null;

            $submittedFields[$field->name] = $submittedValue;
        }

        return $submittedFields;
    }
}
