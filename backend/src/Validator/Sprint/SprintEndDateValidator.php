<?php

namespace App\Validator\Sprint;

use App\Service\Common\ClockInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SprintEndDateValidator extends ConstraintValidator
{

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var SprintEndDate $constraint */
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof DateTimeImmutable) {
            throw new InvalidArgumentException('The value must be an instance of DateTimeImmutable.');
        }

        $now = $this->clock->now();

        if ($now->greaterThan($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ currentDate }}', $value->format('Y-m-d'))
                ->setParameter('{{ now }}', $now->format('Y-m-d'))
                ->addViolation();
        }

    }
}
