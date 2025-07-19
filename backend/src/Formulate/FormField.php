<?php

namespace App\Formulate;

use App\Formulate\Validator\NotBlank;

class FormField
{

    public ?FormFieldErrorInterface $error = null {
        get {
            return $this->error;
        }
    }

    private mixed $value = null;

    private ?NotBlank $notBlankValidator;

    /**
     * @var FieldValidator[]
     */
    private array $validators = [];

    public function __construct(
        public readonly string $name,
        /**
         * @var FieldValidator[] $validators
         */
        array $validators = [],
        public readonly ?FormWidget $widget = null,
        public readonly ?string $label = null,
    ) {
        $this->setValidators($validators);
        $this->notBlankValidator = $this->findNotBlankValidator();
    }

    public function load(mixed $value): void
    {
        $this->value = $value;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function label(): string
    {
        return $this->label ?? $this->name;
    }

    public function validate(Form $form): bool
    {
        if (!$this->isRequired() && empty($this->value)) {
            return true;
        }

        foreach ($this->validators() as $validator) {
            $error = $validator->validate($this, $form);
            if ($error) {
                $this->error = $error;
                return false;
            }
        }

        return true;
    }

    public function isRequired(): bool
    {
        return $this->notBlankValidator !== null;
    }

    /**
     * @return FieldValidator[]
     */
    public function validators(): array
    {
        if (!$this->isRequired()) {
            return $this->validators;
        }

        return array_merge(
            [$this->notBlankValidator],
            array_filter($this->validators, fn($validator) => $validator::class !== NotBlank::class)
        );
    }

    private function findNotBlankValidator(): ?NotBlank
    {
        return array_find($this->validators, fn($validator) => $validator instanceof NotBlank);
    }

    private function setValidators(array $validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
    }

    private function addValidator(FieldValidator $validator): void
    {
        $this->validators[] = $validator;
    }

}
