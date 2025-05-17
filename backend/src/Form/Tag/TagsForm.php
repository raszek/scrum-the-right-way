<?php

namespace App\Form\Tag;

use App\Entity\Issue\Issue;
use App\Entity\Project\ProjectTag;
use App\Helper\StringHelper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

readonly class TagsForm
{

    public function __construct(
        private ?string $tags = null
    ) {
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $tags = $this->tags();

        $tagCount = count($tags);
        if ($tagCount > Issue::MAX_TAG_COUNT) {
            $context->buildViolation(sprintf('Cannot set more than %d tags', $tagCount))
                ->atPath('tags')
                ->addViolation();
        }

        foreach ($tags as $tag) {
            if (StringHelper::length($tag) <= 0) {
                $context->buildViolation('Tag must have at least one character')
                    ->atPath('tags')
                    ->addViolation();
            }

            if (StringHelper::length($tag) > ProjectTag::NAME_MAX_LENGTH) {
                $context->buildViolation(
                    sprintf('Invalid tag "%s". Tag cannot be longer than %d',
                        $tag,
                        ProjectTag::NAME_MAX_LENGTH
                    ))
                    ->atPath('tags')
                    ->addViolation();
            }

            if (!preg_match(ProjectTag::NAME_REGEX, $tag)) {
                $context->buildViolation(sprintf('Invalid tag "%s". Tag must be have _, [a-z], [A-Z] letters only.', $tag))
                    ->atPath('tags')
                    ->addViolation();
            }
        }

    }

    public function tags(): array
    {
        $tags = explode(',', $this->tags);

        if ($tags[0] === '') {
            return [];
        }

        return $tags;
    }
}
