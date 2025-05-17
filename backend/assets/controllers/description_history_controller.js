import { Controller } from '@hotwired/stimulus';
import {get} from 'util';

export default class extends Controller {

    static targets = ['change', 'container', 'loader'];

    connect() {
        this.loadChangesByIndex(0);
    }

    change(e) {
        if (e.target.classList.contains('active')) {
            return;
        }

        this.loadChangesByIndex(e.params.index);
    }

    async loadChangesByIndex(index) {
        this.deactivateRest();

        const change = this.changeTargets[index];

        if (!change) {
            throw new Error('Change not found');
        }

        change.classList.add('active');
        const url = change.getAttribute('data-description-history-url-param');

        this.showLoader();
        try {
            this.containerTarget.innerHTML = await get(url);
        } catch (e) {
            console.log(e);
        }

        this.hideLoader();
    }

    showLoader() {
        this.containerTarget.innerHTML = '';
        this.loaderTarget.style.display = 'flex';
    }

    hideLoader() {
        this.loaderTarget.style.display = 'none';
    }

    deactivateRest() {
        this.changeTargets.forEach((change) => {
            change.classList.remove('active');
        });
    }

}
