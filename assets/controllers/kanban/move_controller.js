import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';
import {isEmpty, post} from 'util';
import {Modal} from 'bootstrap';

export default class extends Controller {

    static values = {
        currentIssue: Object
    }

    static targets = [
        'column',
        'modal',
        'backButton',
        'issue',
        'currentTaskUrl'
    ];

    connect() {
        this.makeColumnSortable();

        this.modal = new Modal(this.modalTarget);
        this.moveBackBinded = this.moveBack.bind(this);
        this.addHideEventListener();

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

        this.moveIssue(
            this.movedIssue.id,
            this.movedIssue.fromColumn,
            this.movedIssue.oldIndex
        );

        this.movedIssue = undefined;
    }

    moveIssue(issueId, columnName, index) {
        const column = this.findColumn(columnName);
        if (!column) {
            throw new Error(`Column "${columnName}" not found`);
        }

        const issueElement = this.findIssue(issueId);
        if (!issueElement) {
            throw new Error('Issue not found');
        }

        const element = column.children[index];
        if (element) {
            element.before(issueElement);
        } else {
            column.append(issueElement);
        }
    }

    async confirmMove() {
        if (!this.movedIssue) {
            throw new Error('No moved issue');
        }

        this.moveBackCurrentIssue();

        this.removeHideEventListener();
        this.modal.hide();
        this.addHideEventListener();

        await this.makeRequest(
            this.movedIssue.moveUrl,
            this.movedIssue.targetColumn,
            this.movedIssue.newIndex
        );

        this.movedIssue = undefined;
    }

    moveBackCurrentIssue() {
        const foundIssue = this.findIssue(this.currentIssueValue.id);
        if (!foundIssue) {
            return;
        }

        const previousColumn = this.getPreviousColumn(this.currentIssueValue.currentColumn);

        this.moveIssue(this.currentIssueValue.id, previousColumn, 0);

        this.currentIssueValue = {
            id: this.movedIssue.id,
            url: this.movedIssue.issueUrl,
            currentColumn: this.movedIssue.targetColumn
        };
    }

    dragIssue(event) {
        const targetColumn = event.to.getAttribute('data-kanban--move-column-key-param');
        const draggedIssueId = event.item.getAttribute('data-kanban--move-id-param');
        const moveUrl = event.item.getAttribute('data-kanban--move-url-param')

        if (!isEmpty(this.currentIssueValue) && this.currentIssueValue.id === draggedIssueId && this.waitColumns().includes(targetColumn)) {
            this.currentIssueValue = {};
        } else if (!isEmpty(this.currentIssueValue) && this.inProgressColumns().includes(targetColumn)) {
            const issueUrl = event.item.getAttribute('data-kanban--move-issue-url-param');
            this.currentTaskUrlTarget.setAttribute('href', issueUrl);

            this.movedIssue = {
                id: draggedIssueId,
                issueUrl,
                moveUrl,
                oldIndex: event.oldIndex,
                newIndex: event.newIndex,
                fromColumn: event.from.getAttribute('data-kanban--move-column-key-param'),
                targetColumn,
            };

            this.modal.show();
            return;
        } else if (isEmpty(this.currentIssueValue) && this.inProgressColumns().includes(targetColumn)) {
            this.currentIssueValue = {
                id: draggedIssueId,
                url: moveUrl,
                currentColumn: targetColumn
            };
        }

        return this.makeRequest(moveUrl, targetColumn, event.newIndex);
    }

    makeRequest(url, targetColumn, index) {
        const formData = new FormData();
        formData.append('position', index + 1);
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

    addHideEventListener() {
        this.modalTarget.addEventListener('hide.bs.modal', this.moveBackBinded);
    }

    removeHideEventListener() {
        this.modalTarget.removeEventListener('hide.bs.modal', this.moveBackBinded);
    }

    getPreviousColumn(currentColumn) {
        const columns = [
            'to-do',
            'in-progress',
            'test',
            'in-tests',
            'done'
        ];

        const index = columns.indexOf(currentColumn);

        if (!columns[index - 1]) {
            throw new Error('Column not exists');
        }

        return columns[index - 1];
    }

    inProgressColumns() {
        return [
            'in-progress',
            'in-tests'
        ];
    }

    waitColumns() {
        return [
            'to-do',
            'test',
            'done'
        ];
    }
}
