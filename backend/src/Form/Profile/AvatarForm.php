<?php

namespace App\Form\Profile;


use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\Validator\ValidatorFactory;

readonly class AvatarForm
{
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
                $v->fileExtension(['png', 'jpg']),
                $v->fileSize(1024 * 1024 * 10),
            ],
        ));

        return $form;
    }

}
