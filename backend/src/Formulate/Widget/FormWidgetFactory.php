<?php

namespace App\Formulate\Widget;

use Twig\Environment;

readonly class FormWidgetFactory
{

    public function __construct(
        private Environment $twig
    ) {
    }

    public function textField(
        array $attributes = [],
    ): TextField {
        return new TextField(
            twig: $this->twig,
            attributes: $attributes,
        );
    }

    public function emailField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            attributes: [
                'type' => 'email',
            ]
        );
    }

    public function passwordField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            attributes: [
                'type' => 'password',
            ]
        );
    }

    public function hiddenField(): TextField
    {
        return new TextField(
            twig: $this->twig,
            template: 'formulate/widget/hidden_input.html.twig',
            attributes: [
                'type' => 'hidden'
            ]
        );
    }

    public function radioField(
        array $options,
        string $template = 'formulate/widget/radio_input.html.twig'
    ): RadioInputField {
        return new RadioInputField(
            twig: $this->twig,
            options: $options,
            template: $template,
        );
    }

    public function siteTextField(string $type = 'text'): TextField
    {
        return new TextField(
            twig: $this->twig,
            template: 'formulate/widget/site_text_input.html.twig',
            attributes: [
                'type' => $type,
            ]
        );
    }

}
