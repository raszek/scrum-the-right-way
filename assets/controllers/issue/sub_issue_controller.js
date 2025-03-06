import { Controller } from '@hotwired/stimulus';
import {post} from 'util';
import {randomString} from 'util';

export default class extends Controller {

    static values = {
        addUrl: String,
    };

    static targets = [
        'button',
        'form',
        'template',
        'list',
        'item'
    ];

    connect() {
        this.resetForm();
    }

    async addSubIssue(e) {
        e.preventDefault();
        const itemId = randomString(6);

        const formData  = new FormData(this.formTarget);

        const clone = this.templateTarget.content.cloneNode(true);

        clone.firstElementChild.setAttribute('data-issue--sub-issue-code-param', itemId);
        clone.firstElementChild.innerText = formData.get('title');

        this.listTarget.prepend(clone);

        const item = this.findItem(itemId);

        this.hideForm();

        try {
            item.outerHTML = await post(this.addUrlValue, formData);
        } catch (e) {
            item.textContent = 'Error';
        }
    }

    async removeSubIssue(e) {
        const subIssueCode = e.params.id;

        const item = this.findItem(subIssueCode);

        if (!item) {
            return;
        }

        const removeUrl = item.getAttribute('data-issue--sub-issue-remove-url-param');

        item.remove();

        return post(removeUrl);
    }


    findItem(itemId) {
        for (const itemTarget of this.itemTargets) {
            if (itemTarget.getAttribute('data-issue--sub-issue-code-param') === itemId) {
                return itemTarget;
            }
        }

        return undefined;
    }

    resetForm() {
        this.formTarget.reset();
    }

    showForm() {
        this.formTarget.classList.remove('d-none');
        this.buttonTarget.classList.add('d-none');
    }

    hideForm() {
        this.formTarget.classList.add('d-none');
        this.buttonTarget.classList.remove('d-none');
        this.resetForm();
    }
}
