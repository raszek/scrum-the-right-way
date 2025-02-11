import { Controller } from '@hotwired/stimulus';
import {post} from 'util';

export default class extends Controller {

    static values = {
        url: String,
        isVisible: Boolean
    };

    static targets = ['item', 'button'];

    connect() {
        super.connect();

        this.isVisible = this.isVisibleValue || false;

        this.display();
    }

    toggle() {
        this.isVisible = !this.isVisible;

        this.display();

        this.request();
    }

    request() {
        if (!this.urlValue) {
            return;
        }

        const data = new FormData;
        data.append('state', this.isVisible ? 'true' : 'false');

        return post(this.urlValue, data);
    }

    display() {
        if (this.isVisible) {
            this.itemTarget.classList.remove('d-none');
            this.buttonTarget.innerHTML = '<i class="bi bi-eye-slash"></i>';
        } else {
            this.itemTarget.classList.add('d-none');
            this.buttonTarget.innerHTML = '<i class="bi bi-eye"></i>';
        }
    }

}
