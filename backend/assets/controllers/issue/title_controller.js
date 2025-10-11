import { Controller } from '@hotwired/stimulus';
import { post } from 'util';

export default class extends Controller {

    static targets = ['text', 'button', 'textarea'];

    static values = {
        url: String,
        maxLength: Number,
        issueId: String
    }

    initialize() {
        this.isEdited = false;
    }

    toggle() {
        if (this.isEdited) {
            this.buttonTarget.innerHTML = '<i class="bi bi-pencil"></i>'
            this.save();
        } else {
            this.buttonTarget.innerHTML = '<i class="bi bi-floppy"></i>'
            this.createTextarea();
        }

        this.isEdited = !this.isEdited;
    }

    createTextarea() {
        const text = this.textTarget.textContent.trim();

        const textarea = document.createElement('textarea');
        textarea.classList.add('form-control');
        textarea.classList.add('strw-update-title-textarea');
        textarea.setAttribute('data-action', 'keydown.enter->issue--title#toggle')
        textarea.setAttribute('maxlength', this.maxLengthValue);
        textarea.setAttribute('data-issue--title-target', 'textarea');
        textarea.addEventListener('input', function () {
            this.style.height = '';
            this.style.height = this.scrollHeight + 'px';
        })
        textarea.textContent = text;

        this.textTarget.innerHTML = '';
        this.textTarget.append(textarea);

        textarea.style.height = '';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    async save() {
        const text = this.textareaTarget.value.trim();

        if (!text) {
            return;
        }

        this.textTarget.textContent = text;

        const formData = new FormData();
        formData.append('title', text);

        await post(this.urlValue, formData);

        this.dispatch('title-changed', {
            detail: {
                issueId: this.issueIdValue,
                title: text
            }
        });
    }
}
