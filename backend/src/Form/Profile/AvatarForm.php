<?php

namespace App\Form\Profile;


use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;

readonly class AvatarForm
{
    public const int AVATAR_MAX_SIZE = 1024 * 1024 * 10;

    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function create(): Form
    {
        $v = $this->validatorFactory;

        $form = new Form(
            data: new AvatarFormData(),
        );

        $form->addField(new FormField(
            name: 'avatar',
            validators: [
                $v->fileExtension(['jpg', 'png']),
                $v->fileSize(self::AVATAR_MAX_SIZE),
            ],
        ));

        return $form;
    }

}
