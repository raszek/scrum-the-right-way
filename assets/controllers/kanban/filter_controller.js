import { Controller } from '@hotwired/stimulus';
import {get} from 'util';

export default class extends Controller {

    static targets = ['button', 'container', 'loader'];

    async filter(event) {
        const currentButton = event.currentTarget;

        if (currentButton.classList.contains('active')) {
            return;
        }

        this.clearButtons();
        currentButton.classList.add('active');

        this.loaderTarget.classList.remove('d-none');
        this.containerTarget.innerHTML = '';

        this.containerTarget.innerHTML = await get(event.params.url);

        this.loaderTarget.classList.add('d-none');
    }

    clearButtons() {
        for (const buttonTarget of this.buttonTargets) {
            buttonTarget.classList.remove('active');
        }
    }
}
