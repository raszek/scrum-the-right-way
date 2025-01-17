import {Controller} from '@hotwired/stimulus';
import {get} from 'util';

export default class extends Controller {

    static targets = ['loader', 'container'];

    static values = {
        url: String
    };

    connect() {
        this.render();
    }

    async render() {
        this.showLoader();
        this.containerTarget.innerHTML = await get(this.urlValue);
        this.hideLoader();
    }

    showLoader() {
        this.loaderTarget.style.display = 'flex';
    }

    hideLoader() {
        this.loaderTarget.style.display = 'none';
    }
}
