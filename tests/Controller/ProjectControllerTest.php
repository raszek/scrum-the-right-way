<?php

namespace App\Tests\Controller;

use App\Entity\Sprint\Sprint;
use App\Enum\Project\ProjectTypeEnum;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectTypeFactory;
use App\Factory\UserFactory;
use App\Repository\Project\ProjectRepository;
use App\Repository\Project\ProjectTypeRepository;
use Zenstruck\Foundry\Test\Factories;

class ProjectControllerTest extends WebTestCase
{

    use Factories;

    /** @test */
    public function user_can_create_sprint_project()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        ProjectTypeFactory::createProjectTypes();

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/app/projects/create');

        $form = $crawler->selectButton('Create')->form();

        $client->submit($form, [
            'project_form[name]' => 'new project name',
            'project_form[code]' => 'NPN',
            'project_form[type]' => $this->projectTypeRepository()->findScrum()->getId(),
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Project "new project name" successfully created.');

        $this->assertPath('/app/projects');

        $createdProject = $this->projectRepository()->findOneBy([
            'name' => 'new project name'
        ]);

        $this->assertNotNull($createdProject);
        $this->assertEquals('NPN', $createdProject->getCode());
        $this->assertEquals($createdProject->getType()->getId(), ProjectTypeEnum::Scrum->value);
        $this->assertTrue($createdProject->hasMember($user));

        $this->assertEquals(1, $createdProject->getSprints()->count());

        /**
         * @var Sprint $firstSprint
         */
        $firstSprint = $createdProject->getSprints()->get(0);

        $this->assertEquals(1, $firstSprint->getNumber());
        $this->assertCount(1, $firstSprint->getSprintGoals());
    }

    /** @test */
    public function user_can_list_his_projects()
    {
        $client = static::createClient();
        $client->followRedirects();

        $projectOneUser = UserFactory::createOne();

        $projectTwoUser = UserFactory::createOne();

        ProjectTypeFactory::createProjectTypes();

        $projectOne = ProjectFactory::createOne([
            'name' => 'project one',
        ]);

        $projectTwo = ProjectFactory::createOne([
            'name' => 'project two',
        ]);

        ProjectMemberFactory::createOne([
            'user' => $projectOneUser,
            'project' => $projectOne
        ]);

        ProjectMemberFactory::createOne([
            'user' => $projectTwoUser,
            'project' => $projectTwo
        ]);

        $this->loginAsUser($projectOneUser);

        $this->goToPage('/app/projects');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('project one');
        $this->assertResponseHasNoText('project two');
    }

    private function projectRepository(): ProjectRepository
    {
        return $this->getService(ProjectRepository::class);
    }

    private function projectTypeRepository(): ProjectTypeRepository
    {
        return $this->getService(ProjectTypeRepository::class);
    }
}
