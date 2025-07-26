<?php

namespace App\Enum\User;

enum UserCodeTypeEnum: string
{

    case Activation = 'activation';

    case ResetPassword = 'reset-password';

    case ChangeEmail = 'change-email';

}
