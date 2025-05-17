<?php

namespace App\Validator\Sprint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class SprintEndDate extends Constraint
{
    public string $message = 'Sprint end date "{{ currentDate }}" cannot be before than "{{ now }}"';

    // You can use #[HasNamedArguments] to make some constraint options required.
    // All configurable options must be passed to the constructor.
    public function __construct(
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }
}
