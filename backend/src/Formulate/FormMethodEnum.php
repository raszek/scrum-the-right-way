<?php

namespace App\Formulate;

enum FormMethodEnum: string
{
    case Get = 'GET';

    case Post = 'POST';


    public function lowercase(): string
    {
        return strtolower($this->value);
    }
}
