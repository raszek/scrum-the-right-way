import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['checkbox', 'button', 'form'];

    connect() {
        this.uncheckAll();
    }

    toggle() {
        const selectedIds = this.checkboxTargets
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);

        this.setIssueIds(selectedIds);

        this.buttonTarget.disabled = selectedIds.length < 1;
    }

    submit(event) {
        const url = event.params.url;
        if (!url) {
            throw new Error('Url not set');
        }

        this.formTarget.setAttribute('action', url);

        this.formTarget.submit();
    }

    setIssueIds(issueIds) {
        this.formTarget.innerHTML = '';

        for (const issueId of issueIds) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'issueIds[]';
            input.value = issueId;

            this.formTarget.appendChild(input);
        }
    }

    uncheckAll() {
        this.buttonTarget.disabled = true;

        this.checkboxTargets.forEach((checkbox) => checkbox.checked = false);
    }
}




