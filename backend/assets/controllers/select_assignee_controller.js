import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {

    static targets = ['select', 'selectContainer', 'textContainer', 'button'];

    static outlets = ['issue-events', 'observer'];

    static values = {
        url: String
    }

    initialize() {
        this.isEdited = false;

        this.tomSelect = new TomSelect(this.selectTarget, {
            plugins: ['no_backspace_delete'],
            create: false,
            onChange: this.selectAssignee.bind(this),
            maxItems: 1
        });
    }

    async selectAssignee(memberId) {
        this.hideEdit();

        if (memberId === '') {
            return;
        }

        if (memberId === 'none') {
            this.textContainerTarget.innerText = 'None';
            await this.request(null);
        } else {
            const fullName = this.tomSelect.getOption(memberId).innerText.trim();
            this.textContainerTarget.innerText = fullName;
            await this.request(memberId);

            const member = {
                id: memberId,
                fullName
            };

            this.addIssueObserver(member);
        }

        await this.renderEvents();
    }

    addIssueObserver(member) {
        if (!this.observerOutlet) {
            return;
        }

        this.observerOutlet.addIssueObserverIfNotExist(member);
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }

    request(memberId) {
        const formData = new FormData();
        if (memberId) {
            formData.append('projectMemberId', memberId);
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

}
