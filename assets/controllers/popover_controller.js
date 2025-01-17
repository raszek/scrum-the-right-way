import { Controller } from '@hotwired/stimulus';
import {Popover} from 'bootstrap';

export default class extends Controller {


    static targets = ['content', 'button'];

    connect() {
        super.connect();

        let content = this.contentTarget.innerHTML;

        const fragment = document.createRange().createContextualFragment(content);

        new Popover(
            this.buttonTarget,
            {
                content: fragment.firstElementChild.innerHTML,
                html: true,
                sanitize: false,
                trigger: 'hover',
                placement: 'top',
            }
        );
    }
}
