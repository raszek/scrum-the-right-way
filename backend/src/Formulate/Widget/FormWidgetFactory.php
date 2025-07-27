<?php

namespace App\Formulate\Widget;

use Twig\Environment;

readonly class FormWidgetFactory
{

    public function __construct(
        private Environment $twig
    ) {
    }

    public function textField(): TextField
    {
        return new TextField(
            twig: $this->twig,
        );
    }

    public function emailField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            type: 'email'
        );
    }

    public function passwordField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            type: 'password'
        );
    }

    public function hiddenField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            type: 'hidden',
            template: 'formulate/widget/hidden_input.html.twig'
        );
    }

    public function siteTextField(string $type = 'text'): TextField
    {
        return new TextField(
            twig: $this->twig,
            type: $type,
            template: 'formulate/widget/site_text_input.html.twig'
        );
    }

}
