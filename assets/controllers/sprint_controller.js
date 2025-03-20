import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';
import {post} from 'util';

export default class extends Controller {

    static targets = ['issues', 'goals'];

    connect() {
        this.makeIssueSortable();
        this.makeGoalSortable();
    }


    sortGoal(event) {

        const goalUrl = event.item.getAttribute('data-sprint-goal-url-param');

        const position = event.newIndex + 1;

        const formData = new FormData;
        formData.append('position', position);

        return post(goalUrl, formData);
    }

    sortIssue(event) {
        const goalId = event.to.getAttribute('data-sprint-goal-id-param');

        const position = event.newIndex + 1;

        const url = event.item.getAttribute('data-sprint-issue-sort-url-param');

        const formData = new FormData;
        formData.append('position', position);
        formData.append('goalId', goalId);

        return post(url, formData);
    }

    makeGoalSortable() {
        for (const goalTarget of this.goalsTargets) {
            new Sortable(goalTarget, {
                group: 'goals',
                animation: 150,
                onUpdate: this.sortGoal.bind(this)
            });
        }
    }

    makeIssueSortable() {
        for (const issueTarget of this.issuesTargets) {
            new Sortable(issueTarget, {
                group: 'issues',
                animation: 150,
                onUpdate: this.sortIssue.bind(this),
                onAdd: this.sortIssue.bind(this),
            });
        }
    }

}
