import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    toggleRole(event) {
        const checked = event.target.checked;

        if (checked) {
            return fetch(event.params.addUrl, {
                method: 'POST'
            })
        } else {
            return fetch(event.params.removeUrl, {
                method: 'POST'
            })
        }
    }
}
