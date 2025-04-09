import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';
import {post} from 'util';
import {Modal} from 'bootstrap';

export default class extends Controller {

    static values = {
        userCurrentIssue: String
    }

    static targets = ['column', 'modal'];

    connect() {
        this.makeColumnSortable();

        this.modal = new Modal(this.modalTarget);
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

    moveIssue(issueId, targetColumn) {

    }

    dragIssue(event) {
        const targetColumn = event.to.getAttribute('data-kanban--move-column-key-param');

        if (['in-progress', 'in-tests'].includes(targetColumn)) {

            return;
        }

        const url = event.item.getAttribute('data-kanban--move-url-param')

        const formData = new FormData();
        formData.append('position', event.newIndex + 1);
        formData.append('column', targetColumn);

        return post(url, formData);
    }

}
