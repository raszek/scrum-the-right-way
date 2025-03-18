import { Controller } from '@hotwired/stimulus';

export default class extends Controller {


    static targets = ['goal', 'issue'];

    connect() {
        this.makeIssuesDraggable();
        this.makeGoalsDroppable();
    }

    onIssueStartDrag(event) {
        const issueCode = event.target.getAttribute('data-sprint-issue-code-param');
        const issueGoalId = event.target.getAttribute('data-sprint-issue-goal-id-param');

        event.dataTransfer.setData('application/issue-code', issueCode);
        event.dataTransfer.setData('application/issue-goal-id', issueGoalId);
        event.dataTransfer.effectAllowed = 'move';
    }


    onGoalEnter(event) {
        event.preventDefault();

        const issueGoalId = event.dataTransfer.getData('application/issue-goal-id');
        const goalId = event.target.getAttribute('data-sprint-goal-id-param');

        if (goalId && goalId !== issueGoalId) {
            event.target.style.border = '3px dotted red';
        }
    }

    onGoalLeave(event) {
        event.preventDefault();

        const issueGoalId = event.dataTransfer.getData('application/issue-goal-id');
        const goalId = event.target.getAttribute('data-sprint-goal-id-param');

        if (goalId && goalId !== issueGoalId) {
            event.target.style.border = '';
        }
    }

    handleIssueDrop(event) {
        event.preventDefault();

        event.target.style.border = '';

        const issueCode = event.dataTransfer.getData('application/issue-code');
        const issueGoalId = event.dataTransfer.getData('application/issue-goal-id');
        const goalId = event.target.getAttribute('data-sprint-goal-id-param');

        if (goalId === issueGoalId) {
            return;
        }

        const issue = this.findIssue(issueCode);

        if (!issue) {
            throw new Error('Issue not found');
        }

        const ul = event.target.querySelector('ul');

        const movedIssue = issue.cloneNode(true);
        movedIssue.setAttribute('data-sprint-issue-goal-id-param', goalId)
        this.makeIssueDraggable(movedIssue);

        ul.append(movedIssue);

        issue.remove();
    }

    findIssue(issueCode) {
        for (const issueTarget of this.issueTargets) {
            if (issueTarget.getAttribute('data-sprint-issue-code-param') === issueCode) {
                return issueTarget;
            }
        }

        return undefined;
    }

    onIssueDragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }

    makeIssuesDraggable() {
        for (const issueTarget of this.issueTargets) {
            this.makeIssueDraggable(issueTarget);
        }
    }

    makeIssueDraggable(issue) {
        issue.draggable = true;
        issue.ondragstart = this.onIssueStartDrag.bind(this)
    }

    makeGoalsDroppable() {
        for (const goalTarget of this.goalTargets) {
            goalTarget.ondrop = this.handleIssueDrop.bind(this);
            goalTarget.ondragover = this.onIssueDragOver.bind(this);
            goalTarget.ondragenter = this.onGoalEnter.bind(this);
            goalTarget.ondragleave = this.onGoalLeave.bind(this);
        }
    }
}
