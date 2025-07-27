<?php

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Exception\User\CannotUseCodeException;
use App\Repository\User\UserCodeRepository;
use App\Service\Common\ClockInterface;

readonly class UserCodeService
{

    public function __construct(
        private UserCodeRepository $userCodeRepository,
        private ClockInterface $clock
    ) {
    }

    /**
     * @param string $code
     * @param UserCodeTypeEnum $type
     * @param User|null $user
     * @return UserCode
     * @throws CannotUseCodeException
     */
    public function useCode(string $code, UserCodeTypeEnum $type, ?User $user): UserCode
    {
        if (!$user) {
            throw new CannotUseCodeException('Invalid activation code');
        }

        $userCode = $this->userCodeRepository->findLatestCode(
            activationCode: $code,
            type: $type,
            user: $user
        );

        if ($userCode === null) {
            throw new CannotUseCodeException('Invalid code');
        }

        if ($this->clock->now()->greaterThan($userCode->getCreatedAt()->addHour())) {
            throw new CannotUseCodeException('Code expired');
        }

        if ($userCode->getUsedAt() !== null) {
            throw new CannotUseCodeException('Code is already used');
        }

        $userCode->setUsedAt($this->clock->now());

        return $userCode;
    }

}
