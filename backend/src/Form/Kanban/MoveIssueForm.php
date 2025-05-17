<?php

namespace App\Form\Kanban;

use App\Enum\Issue\IssueColumnEnum;
use App\Helper\ArrayHelper;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

readonly class MoveIssueForm
{

    public function __construct(
        #[NotBlank]
        #[GreaterThan(0)]
        #[Type('integer')]
        public int $position,
        #[NotBlank]
        public string $column
    ) {
    }

    #[Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $kanbanColumns = IssueColumnEnum::kanbanColumns();

        $kanbanKeys = ArrayHelper::map($kanbanColumns, fn(IssueColumnEnum $kanbanColumn) => $kanbanColumn->key());

        if (!in_array($this->column, $kanbanKeys)) {
            $context->buildViolation('This is not kanban column.')
                ->atPath('column')
                ->addViolation();
        }
    }

}
