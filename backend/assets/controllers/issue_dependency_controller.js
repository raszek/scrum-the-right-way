import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';
import {get, post} from 'util';

export default class extends Controller {

    static outlets = ['issue-events'];

    static targets = [
        'addButton',
        'selectContainer',
        'select',
        'dependencyTemplate',
        'container',
        'item',
        'emptyText'
    ];

    static values = {
        fetchUrl: String,
    }

    connect() {
        this.tomSelect = new TomSelect(this.selectTarget, {
            load: this.fetchIssueDependencies.bind(this),
            onChange: this.addDependency.bind(this),
            render: {
                option: function (data, escape) {
                    return `
                    <div data-dependency-url="${escape(data.url)}"
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

    async fetchIssueDependencies(query, callback) {

        const encodedQuery = encodeURIComponent(query);

        const url = `${this.fetchUrlValue}?search=${encodedQuery}`

        try {
            const data = JSON.parse(await get(url));

            callback(data);
        } catch (e) {
            callback();
        }
    }

    async addDependency(value) {
        const option = this.tomSelect.getOption(value);

        this.hideForm();
        const newDependency = this.dependencyTemplateTarget.content.cloneNode(true);

        const removeUrl = option.getAttribute('data-remove-url');

        const li = newDependency.firstElementChild;
        li.setAttribute('data-issue-dependency-id-param', value);
        li.setAttribute('data-issue-dependency-remove-url-param', removeUrl);

        const url = option.getAttribute('data-dependency-url');
        const link = li.getElementsByTagName('a')[0];
        link.setAttribute('href', url);
        link.textContent = option.textContent;

        const button = li.getElementsByTagName('button')[0];
        button.setAttribute('data-confirm-dialog-url-param', value);

        this.containerTarget.append(newDependency);

        this.renderEmptyText();

        const addUrl = option.getAttribute('data-add-url');

        await post(addUrl);

        this.renderEvents();
    }

    async removeDependency(event) {
        event.preventDefault();

        const item = this.findItem(String(event.params.id));

        if (!item) {
            return;
        }

        const removeUrl = item.getAttribute('data-issue-dependency-remove-url-param');

        item.remove();

        this.renderEmptyText();

        await post(removeUrl);

        this.renderEvents();
    }

    findItem(dependencyId) {
        for (const item of this.itemTargets) {

            const itemId = item.getAttribute('data-issue-dependency-id-param');

            if (itemId === dependencyId) {
                return item;
            }
        }

        return undefined;
    }

    renderEmptyText() {
        if (this.itemTargets.length <= 0) {
            this.emptyTextTarget.classList.remove('d-none')
        } else {
            this.emptyTextTarget.classList.add('d-none')
        }
    }

    showForm() {
        this.selectContainerTarget.classList.remove('d-none');
        this.addButtonTarget.classList.add('d-none');
    }

    hideForm() {
        this.selectContainerTarget.classList.add('d-none');
        this.addButtonTarget.classList.remove('d-none');
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }
}
