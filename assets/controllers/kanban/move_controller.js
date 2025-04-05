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
            });
        }
    }

}
