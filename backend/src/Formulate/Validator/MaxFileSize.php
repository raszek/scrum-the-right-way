<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;
use App\Helper\StringHelper;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class MaxFileSize implements FieldValidator
{

    public function __construct(
        private int $maxBytes
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        $value = $field->value();

        if (!$value instanceof UploadedFile) {
            throw new RuntimeException('Field value must be an instance of UploadedFile');
        }

        if ($value->getSize() > $this->maxBytes) {
            return new FormFieldError(sprintf('%s must be less than %s', $field->label(), StringHelper::readableBytes($this->maxBytes)));
        }

        return null;
    }
}
