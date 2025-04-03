import { Controller } from '@hotwired/stimulus';
import {Sortable} from 'sortablejs';

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
                // onUpdate: this.sortIssue.bind(this),
                // onAdd: this.sortIssue.bind(this),
            });
        }
    }

    // sortIssue(event) {
    //     const goalId = event.to.getAttribute('data-sprint--sort-goal-id-param');
    //
    //     const position = event.newIndex + 1;
    //
    //     const url = event.item.getAttribute('data-sprint--sort-issue-sort-url-param');
    //
    //     const formData = new FormData;
    //     formData.append('position', position);
    //     formData.append('goalId', goalId);
    //
    //     return post(url, formData);
    // }


}
