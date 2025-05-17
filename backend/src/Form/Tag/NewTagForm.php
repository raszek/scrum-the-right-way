<?php

namespace App\Form\Tag;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectTag;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['project', 'name'],
    message: 'This tag is already used in this project',
    entityClass: ProjectTag::class
)]
class NewTagForm
{

    public function __construct(
        public Project $project,
        #[Assert\NotBlank()]
        public ?string $name = null,
        #[Assert\NotBlank()]
        #[Assert\CssColor()]
        public ?string $backgroundColor = null
    ) {
    }


    public static function fromRequest(Request $request, Project $project): static
    {
        return new NewTagForm(
            project: $project,
            name: $request->get('name'),
            backgroundColor: $request->get('backgroundColor'),
        );
    }
}
