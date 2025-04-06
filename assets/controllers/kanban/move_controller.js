import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';
import {post} from 'util';

export default class extends Controller {

    static targets = ['column'];

    connect() {
        this.makeColumnSortable();
    }

    makeColumnSortable() {
        for (const columnTarget of this.columnTargets) {

            new Sortable(columnTarget, {
                group: 'issue',
                animation: 150,
                onUpdate: this.moveIssue.bind(this),
                onAdd: this.moveIssue.bind(this),
            });
        }
    }

    moveIssue(event) {
        const targetColumn = event.to.getAttribute('data-kanban--move-column-key-param');

        const url = event.item.getAttribute('data-kanban--move-url-param')

        const formData = new FormData();
        formData.append('position', event.newIndex + 1);
        formData.append('column', targetColumn);

        return post(url, formData);
    }

}
