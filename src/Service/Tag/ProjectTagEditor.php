<?php

namespace App\Service\Tag;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectTag;
use App\Form\Tag\NewTagForm;
use App\ValueObject\Color;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectTagEditor
{

    public function __construct(
        private Project $project,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function addTag(NewTagForm $form): void
    {
        $createdTag = new ProjectTag(
            name: $form->name,
            backgroundColor: Color::fromHex($form->backgroundColor),
            project: $this->project
        );

        $this->entityManager->persist($createdTag);

        $this->entityManager->flush();
    }

}
