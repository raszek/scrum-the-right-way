import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';
import {post} from 'util';
import {Modal} from 'bootstrap';

export default class extends Controller {

    static values = {
        currentIssueId: String
    }

    static targets = [
        'column',
        'modal',
        'backButton',
        'issue'
    ];

    connect() {
        this.makeColumnSortable();

        this.modal = new Modal(this.modalTarget);
        this.modalTarget.addEventListener('hide.bs.modal', this.moveBack.bind(this));

        this.movedIssue = undefined;
    }

    makeColumnSortable() {
        for (const columnTarget of this.columnTargets) {

            new Sortable(columnTarget, {
                group: 'issue',
                animation: 150,
                onUpdate: this.dragIssue.bind(this),
                onAdd: this.dragIssue.bind(this),
            });
        }
    }

    moveBack() {
        if (!this.movedIssue) {
            throw new Error('No moved issue');
        }

        const column = this.findColumn(this.movedIssue.fromColumn);
        if (!column) {
            throw new Error(`Column "${this.movedIssue.fromColumn}" not found`);
        }

        const issueElement = this.findIssue(this.movedIssue.id);
        if (!issueElement) {
            throw new Error('Issue not found');
        }

        const element = column.children[this.movedIssue.oldIndex];
        if (element) {
            element.before(issueElement);
        } else {
            column.append(issueElement);
        }
    }

    dragIssue(event) {
        const targetColumn = event.to.getAttribute('data-kanban--move-column-key-param');

        if (this.currentIssueIdValue && ['in-progress', 'in-tests'].includes(targetColumn)) {
            this.movedIssue = {
                id: event.item.getAttribute('data-kanban--move-id-param'),
                oldIndex: event.oldIndex,
                fromColumn: event.from.getAttribute('data-kanban--move-column-key-param'),
            };

            this.modal.show();
            return;
        }

        const url = event.item.getAttribute('data-kanban--move-url-param')

        const formData = new FormData();
        formData.append('position', event.newIndex + 1);
        formData.append('column', targetColumn);

        return post(url, formData);
    }

    findIssue(issueId) {
        for (const issueElement of this.issueTargets) {
            if (issueElement.getAttribute('data-kanban--move-id-param') === issueId) {
                return issueElement;
            }
        }

        return undefined;
    }

    findColumn(columnKey) {
        for (const columnTarget of this.columnTargets) {
            if (columnKey === columnTarget.getAttribute('data-kanban--move-column-key-param')) {
                return columnTarget;
            }
        }

        return undefined;
    }
}
