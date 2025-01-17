<?php

namespace App\Tests\Repository\Thread;

use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueThreadMessageFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Thread\ThreadFactory;
use App\Factory\Thread\ThreadMessageFactory;
use App\Repository\Thread\ThreadMessageRepository;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ThreadMessageRepositoryTest extends KernelTestCase
{

    use Factories;

    /** @test */
    public function user_can_fuzzy_search_threads()
    {
        $project = ProjectFactory::createOne();

        $thread = ThreadFactory::createOne([
            'title' => 'Blanditiis temporibus natus iste nulla minima cupiditate possimus iusto. Modi architecto eum qui doloribus.',
            'project' => $project
        ]);

        $searchedThreadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'number' => 1
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $thread,
            'number' => 2
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project
        ]);

        $repository = $this->create();

        $result = $repository->searchIssueMessages('blanditiis #1', $issue);

        $this->assertCount(1, $result);

        $foundThreadMessage = $result[0];

        $this->assertEquals($searchedThreadMessage->getId(), $foundThreadMessage->getId());
    }

    /** @test */
    public function user_cannot_search_thread_messages_already_added_to_issue()
    {
        $project = ProjectFactory::createOne();

        $thread = ThreadFactory::createOne([
            'title' => 'Blanditiis temporibus natus iste nulla minima cupiditate possimus iusto. Modi architecto eum qui doloribus.',
            'project' => $project
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'number' => 1
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
        ]);

        IssueThreadMessageFactory::createOne([
            'issue' => $issue,
            'threadMessage' => $threadMessage
        ]);

        $repository = $this->create();

        $result = $repository->searchIssueMessages('blanditiis', $issue);

        $this->assertCount(0, $result);
    }

    private function create(): ThreadMessageRepository
    {
        return $this->getService(ThreadMessageRepository::class);
    }
}
