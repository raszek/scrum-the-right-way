<?php

namespace App\Tests\Controller;

use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Repository\Project\ProjectTagRepository;
use Zenstruck\Foundry\Test\Factories;

class ProjectTagControllerTest extends WebTestCase
{
    use Factories;

    /** @test */
    public function analytic_can_create_new_tag()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/tags',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'name' => 'CODE_REVIEW',
            'backgroundColor' => '#f00'
        ]);

        $this->assertResponseStatusCodeSame(204);

        $projectTags = $this->projectTagRepository()->findBy([
            'project' => $project,
        ]);

        $this->assertCount(1, $projectTags);

        $createdProjectTag = $projectTags[0];

        $this->assertEquals('CODE_REVIEW', $createdProjectTag->getName());
        $this->assertEquals('#ff0000', $createdProjectTag->getBackgroundColor());
    }

    private function projectTagRepository(): ProjectTagRepository
    {
        return $this->getService(ProjectTagRepository::class);
    }
}
