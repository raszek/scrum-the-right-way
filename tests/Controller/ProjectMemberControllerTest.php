<?php

namespace App\Tests\Controller;


use App\Enum\Project\ProjectRoleEnum;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use App\Repository\Project\ProjectMemberRepository;
use Zenstruck\Foundry\Test\Factories;

class ProjectMemberControllerTest extends WebTestCase
{

    use Factories;

    /** @test */
    public function project_member_can_access_member_list()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne([
            'firstName' => 'Adminfirstname',
            'lastName' => 'Adminlastname',
        ]);

        $analytic = UserFactory::createOne([
            'firstName' => 'Analyticfirstname',
            'lastName' => 'Analyticlastname',
        ]);

        $developer = UserFactory::createOne([
            'firstName' => 'Developerfirstname',
            'lastName' => 'Developerlastname',
        ]);

        $project = ProjectFactory::createOne();

        $anotherProject = ProjectFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $anotherProject
        ]);

        $this->loginAsUser($analytic);

        $this->goToPage('/projects/' . $project->getId() . '/members');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Adminfirstname Adminlastname');
        $this->assertResponseHasText('Analyticfirstname Analyticlastname');
        $this->assertResponseHasNotText('Developerfirstname Developerlastname');
    }

    /** @test */
    public function member_can_search_through_project_members()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne([
            'firstName' => 'Adminfirstname',
            'lastName' => 'Adminlastname',
            'email' => 'admin@example.com',
        ]);

        $analytic = UserFactory::createOne([
            'firstName' => 'Analyticfirstname',
            'lastName' => 'Analyticlastname',
            'email' => 'analytic@example.com',
        ]);

        $developer = UserFactory::createOne([
            'firstName' => 'Developerfirstname',
            'lastName' => 'Developerlastname',
            'email' => 'developer@example.com',
        ]);

        $project = ProjectFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $this->loginAsUser($analytic);

        $crawler = $this->goToPageSafe('/projects/' . $project->getId() . '/members');

        $searchForm = $crawler->selectButton('Search')->form();

        $client->submit($searchForm, [
            'project_member_search[name]' => 'Developer'
        ]);

        $this->assertResponseIsSuccessful();

        $tableRows = $this->readTable('table');

        $this->assertEquals('Developerfirstname Developerlastname', $tableRows[1][0]);
        $this->assertEquals('developer@example.com', $tableRows[1][1]);
    }

    /** @test */
    public function member_admin_can_add_roles_to_project_members()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $projectAdminRole = ProjectRoleFactory::adminRole();
        ProjectRoleFactory::analyticRole();

        $projectAdminMember = ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $projectAdminMember,
            'role' => $projectAdminRole
        ]);

        $analyticMember = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $this->loginAsUser($admin);

        $url = sprintf(
            '/projects/%s/members/%s/roles/%s/add',
            $project->getId(),
            $analyticMember->getId(),
            ProjectRoleEnum::Analytic->value
        );

        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(204);

        $updatedAnalytic = $this->projectMemberRepository()->findOneBy([
            'id' => $analyticMember->getId()
        ]);

        $this->assertNotNull($updatedAnalytic);
        $this->assertTrue($updatedAnalytic->isAnalytic());
    }

    /** @test */
    public function member_admin_can_remove_roles_from_project_members()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $analyticRole = ProjectRoleFactory::analyticRole();

        $adminRole = ProjectRoleFactory::adminRole();

        $adminMember = ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        $analyticMember = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $adminRole,
            'projectMember' => $adminMember
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $analyticRole,
            'projectMember' => $analyticMember
        ]);

        $this->loginAsUser($admin);

        $url = sprintf(
            '/projects/%s/members/%s/roles/%s/remove',
            $project->getId(),
            $analyticMember->getId(),
            ProjectRoleEnum::Analytic->value
        );

        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(204);

        $updatedAnalytic = $this->projectMemberRepository()->findOneBy([
            'id' => $analyticMember->getId()
        ]);

        $this->assertNotNull($updatedAnalytic);
        $this->assertFalse($updatedAnalytic->isAnalytic());
    }

    /** @test */
    public function member_admin_can_remove_analytic_from_project()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $adminRole = ProjectRoleFactory::adminRole();

        $analyticRole = ProjectRoleFactory::analyticRole();

        $adminMember = ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        $analyticMember = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $adminRole,
            'projectMember' => $adminMember
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $analyticRole,
            'projectMember' => $analyticMember
        ]);

        $removedMemberId = $analyticMember->getId();

        $this->loginAsUser($admin);

        $url = sprintf(
            '/projects/%s/members/%s/remove',
            $project->getId(),
            $analyticMember->getId(),
        );

        $client->request('POST', $url);

        $this->assertResponseIsSuccessful();

        $removedMember = $this->projectMemberRepository()->findOneBy([
            'id' => $removedMemberId
        ]);

        $this->assertNull($removedMember);
    }

    /** @test */
    public function member_admin_can_add_new_project_member()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne();

        $newUser = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $adminRole = ProjectRoleFactory::adminRole();

        $adminMember = ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $adminRole,
            'projectMember' => $adminMember
        ]);

        $this->loginAsUser($admin);

        $crawler = $this->goToPageSafe('/projects/' . $project->getId() . '/members');

        $form = $crawler->selectButton('Add member')->form();

        $client->submit($form, [
            'add_project_member[email]' => $newUser->getEmail(),
        ]);

        $this->assertResponseIsSuccessful();

        $addedMember = $this->projectMemberRepository()->findOneBy([
            'user' => $newUser->getId(),
        ]);

        $this->assertNotNull($addedMember);
        $this->assertEquals($addedMember->getProject()->getId(), $project->getId());
    }

    /** @test */
    public function member_admin_can_search_for_non_member_users()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::createOne([
            'firstName' => 'Admin',
            'lastName' => 'Admin',
            'email' => 'admin@admin.com',
        ]);

        UserFactory::createOne([
            'firstName' => 'firstname',
            'lastName' => 'lastname',
            'email' => 'email@email.com',
        ]);

        $project = ProjectFactory::createOne();

        $adminRole = ProjectRoleFactory::adminRole();

        $adminMember = ProjectMemberFactory::createOne([
            'user' => $admin,
            'project' => $project
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $adminRole,
            'projectMember' => $adminMember
        ]);

        $this->loginAsUser($admin);

        $client->request('GET', '/projects/' . $project->getId() . '/non-members?query=first');

        $this->assertResponseIsSuccessful();

        $reality = JsonHelper::decode($client->getResponse()->getContent());

        $expectations = [
            'results' => [
                [
                    'value' => 'email@email.com',
                    'text' => 'firstname lastname "email@email.com"',
                ]
            ]
        ];

        $this->assertEquals($expectations, $reality);
    }

    private function projectMemberRepository(): ProjectMemberRepository
    {
        return $this->getService(ProjectMemberRepository::class);
    }
}
