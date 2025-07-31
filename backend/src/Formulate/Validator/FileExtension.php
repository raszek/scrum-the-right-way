<?php

namespace App\Formulate\Validator;


use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileExtension implements FieldValidator
{

    public function __construct(
        private array $extensions
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        $value = $field->value();

        if (!$value instanceof UploadedFile) {
            throw new RuntimeException('Field value must be an instance of UploadedFile');
        }

        if (!in_array($value->getExtension(), $this->extensions)) {
            return new FormFieldError(
                sprintf('%s must be a file with one of the following extensions: %s',
                $field->label(),
                implode(', ', $this->extensions))
            );
        }

        $extensionMimeType = $this->getExtensionMimeType($value->getExtension());

        if ($value->getMimeType() !== $extensionMimeType) {
            return new FormFieldError(sprintf('Invalid mime type. Extension suggests %s mimetype', $extensionMimeType));
        }

        return null;
    }

    private function getExtensionMimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        };
    }
}
