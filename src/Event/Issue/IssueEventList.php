<?php

namespace App\Event\Issue;


use App\Event\EventList;
use App\Event\Issue\Renderer\AddIssueThreadMessageEventRenderer;
use App\Event\Issue\Renderer\CreateIssueEventRenderer;
use App\Event\Issue\Renderer\RemoveIssueThreadMessageEventRenderer;
use App\Event\Issue\Renderer\SetIssueAssigneeEventRenderer;
use App\Event\Issue\Renderer\SetIssueDescriptionEventRenderer;
use App\Event\Issue\Renderer\SetIssueStoryPointsEventRenderer;
use App\Event\Issue\Renderer\SetIssueTagsEventRenderer;

class IssueEventList implements EventList
{

    const CREATE_ISSUE = 'CREATE_ISSUE';

    const SET_ISSUE_ASSIGNEE = 'SET_ISSUE_ASSIGNEE';

    const SET_ISSUE_DESCRIPTION = 'SET_ISSUE_DESCRIPTION';

    const SET_ISSUE_STORY_POINTS = 'SET_ISSUE_STORY_POINTS';

    const SET_ISSUE_TAGS = 'SET_ISSUE_TAGS';

    const ADD_ISSUE_THREAD_MESSAGE = 'ADD_ISSUE_THREAD_MESSAGE';

    const REMOVE_ISSUE_THREAD_MESSAGE = 'REMOVE_ISSUE_THREAD_MESSAGE';

    public static function rendererClasses(): array
    {
        return [
            self::CREATE_ISSUE => CreateIssueEventRenderer::class,
            self::SET_ISSUE_ASSIGNEE => SetIssueAssigneeEventRenderer::class,
            self::SET_ISSUE_DESCRIPTION => SetIssueDescriptionEventRenderer::class,
            self::SET_ISSUE_STORY_POINTS => SetIssueStoryPointsEventRenderer::class,
            self::SET_ISSUE_TAGS => SetIssueTagsEventRenderer::class,
            self::ADD_ISSUE_THREAD_MESSAGE => AddIssueThreadMessageEventRenderer::class,
            self::REMOVE_ISSUE_THREAD_MESSAGE => RemoveIssueThreadMessageEventRenderer::class,
        ];
    }

    public function labels(): array
    {
        return [
            'Create issue' => self::CREATE_ISSUE,
            'Set assignee' => self::SET_ISSUE_ASSIGNEE,
            'Set description' => self::SET_ISSUE_DESCRIPTION,
            'Set story points' => self::SET_ISSUE_STORY_POINTS,
            'Set tags' => self::SET_ISSUE_TAGS,
            'Add issue thread message' => self::ADD_ISSUE_THREAD_MESSAGE,
            'Remove issue thread message' => self::REMOVE_ISSUE_THREAD_MESSAGE,
        ];
    }
}
