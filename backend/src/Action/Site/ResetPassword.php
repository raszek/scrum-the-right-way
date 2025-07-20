<?php

namespace App\Action\Site;

use App\Form\Site\ResetPasswordForm;
use App\Service\Site\SiteService;

readonly class ResetPassword
{

    public function __construct(
        private SiteService $siteService,
    ) {
    }

    /**
     * @param ResetPasswordForm $resetPasswordForm
     * @return void
     * @throws \App\Exception\Site\CannotResetPasswordException
     */
    public function execute(ResetPasswordForm $resetPasswordForm): void
    {
        $this->siteService->resetPassword($resetPasswordForm);
    }

}
