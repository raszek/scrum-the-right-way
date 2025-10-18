import { Controller } from '@hotwired/stimulus';
import {Modal} from 'bootstrap';
import {get} from 'util';

export default class extends Controller {

    static targets = ['modal', 'content', 'loader', 'title'];

    connect() {
        this.modal = new Modal(this.modalTarget);
    }

    async show(e) {
        const params = e.params;

        this.titleTarget.innerText = params.code;

        this.modal.show();

        this.showLoader();

        this.contentTarget.innerHTML = await get(params.url);
        console.log(this.contentTarget.innerHTML);

        this.hideLoader();
    }

    showLoader() {
        if (!this.loaderTarget.classList.contains('d-none')) {
            return;
        }

        this.loaderTarget.classList.remove('d-none');
    }

    hideLoader() {
        this.loaderTarget.classList.add('d-none');
    }
}
