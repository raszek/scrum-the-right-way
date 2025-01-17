import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';
import {get, post} from 'util';

export default class extends Controller {

    static outlets = ['issue-events'];

    static targets = [
        'addButton',
        'selectContainer',
        'select',
        'messageTemplate',
        'container',
        'item',
        'emptyText'
    ];

    static values = {
        fetchUrl: String,
    }

    connect() {
        this.tomSelect = new TomSelect(this.selectTarget, {
            load: this.fetchThreadMessages.bind(this),
            onChange: this.addThreadMessage.bind(this),
            render: {
                option: function (data, escape) {
                    return `
                    <div data-thread-url="${escape(data.url)}"
                         data-add-url="${escape(data.addUrl)}"
                         data-remove-url="${escape(data.removeUrl)}"
                    >
                      ${escape(data.text)}
                    </div>
                    `;
                }
            }
        });
    }

    async fetchThreadMessages(query, callback) {

        const encodedQuery = encodeURIComponent(query);

        const url = `${this.fetchUrlValue}?search=${encodedQuery}`

        try {
            const data = JSON.parse(await get(url));

            callback(data);
        } catch (e) {
            callback();
        }
    }

    cancel() {
        this.hideForm();
    }

    async addThreadMessage(value) {
        const option = this.tomSelect.getOption(value);

        const url = option.getAttribute('data-thread-url');
        const removeUrl = option.getAttribute('data-remove-url');

        const newThreadMessage = this.messageTemplateTarget.content.cloneNode(true);

        const li = newThreadMessage.firstElementChild;
        li.setAttribute('data-issue-threads-id-param', value);
        li.setAttribute('data-issue-threads-remove-url-param', removeUrl);

        const link = li.getElementsByTagName('a')[0];
        link.setAttribute('href', url);
        link.textContent = option.textContent;

        const button = li.getElementsByTagName('button')[0];
        button.setAttribute('data-confirm-dialog-url-param', value);

        this.containerTarget.append(newThreadMessage);

        this.hideForm();

        const addUrl = option.getAttribute('data-add-url');

        this.renderEmptyText();

        await post(addUrl);

        this.renderEvents();
    }

    async removeThreadMessage(event) {
        event.preventDefault();

        const item = this.findItem(event.params.id);

        if (!item) {
            return;
        }

        const removeUrl = item.getAttribute('data-issue-threads-remove-url-param');

        item.remove();

        this.renderEmptyText();

        await post(removeUrl);

        this.renderEvents();
    }

    renderEmptyText() {
        if (this.itemTargets.length <= 0) {
            this.emptyTextTarget.classList.remove('d-none')
        } else {
            this.emptyTextTarget.classList.add('d-none')
        }
    }

    findItem(messageId) {
        for (const item of this.itemTargets) {

            const itemId = item.getAttribute('data-issue-threads-id-param');

            if (itemId === messageId) {
                return item;
            }
        }

        return undefined;
    }

    hideForm() {
        this.addButtonTarget.classList.remove('d-none');
        this.selectContainerTarget.classList.add('d-none');
    }

    showForm() {
        this.addButtonTarget.classList.add('d-none');
        this.selectContainerTarget.classList.remove('d-none');

        this.tomSelect.focus();
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }
}
