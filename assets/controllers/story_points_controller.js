import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {

    static targets = ['select', 'selectContainer', 'textContainer', 'button'];

    static outlets = ['issue-events'];

    static values = {
        url: String
    }

    connect() {
        this.isEdited = false;

        this.tomSelect = new TomSelect(this.selectTarget, {
            create: true,
            createFilter: /^[1-9][0-9]*$/,
            maxItems: 1,
            onChange: this.selectStoryPoints.bind(this),
        });
    }

    async selectStoryPoints(storyPoints) {
        if (storyPoints === '') {
            return;
        }

        this.hideEdit();

        await this.setStoryPoints(storyPoints);

        this.renderEvents();
    }

    async setStoryPoints(storyPoints) {
        if (storyPoints === 'none') {
            this.textContainerTarget.innerText = 'None';
            return this.request(null);
        }

        this.textContainerTarget.innerText = this.tomSelect.getOption(storyPoints).innerText.trim();
        return this.request(storyPoints);
    }

    async request(storyPoints) {
        const formData = new FormData();
        if (storyPoints) {
            formData.append('points', storyPoints);
        }

        return fetch(this.urlValue, {
            method: 'POST',
            body: formData
        })
    }

    hideEdit() {
        this.selectContainerTarget.style.display = 'none';
        this.textContainerTarget.style.display = 'block';
        this.buttonTarget.innerHTML = '<i class="bi bi-pencil"></i>';

        this.isEdited = false;
    }

    showEdit() {
        this.tomSelect.clear();
        this.tomSelect.focus();
        this.selectContainerTarget.style.display = 'block';
        this.textContainerTarget.style.display = 'none';
        this.buttonTarget.innerHTML = '<i class="bi bi-x"></i>';

        this.isEdited = true;
    }

    toggle() {
        if (this.isEdited) {
            this.hideEdit();
        } else {
            this.showEdit();
        }
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }
}
