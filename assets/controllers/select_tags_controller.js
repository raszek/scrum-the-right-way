import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';
import {isArrayEqual, post, randomColor, textColorBasedOnBackground} from 'util';

export default class extends Controller {

    static outlets = ['issue-events'];

    static targets = [
        'select',
        'selectContainer',
        'list',
        'editButton',
        'saveButton',
        'tagTemplate',
        'emptyText'
    ];

    static values = {
        maxLength: Number,
        maxItems: Number,
        url: String,
        createUrl: String,
    }

    connect() {
        this.tomSelect = new TomSelect(this.selectTarget, {
            plugins: {
                remove_button:{
                    title: 'Remove this item',
                }
            },
            create: this.itemCreate.bind(this),
            createFilter: this.validateTag.bind(this),
            maxItems: this.maxItemsValue,
            onOptionAdd: this.optionAdd.bind(this),
            render: {
                item: function(data) {
                    const div = document.createElement('div');
                    div.textContent = data.text;
                    if (data.backgroundColor) {
                        div.style.backgroundColor = data.backgroundColor;
                        div.style.color = textColorBasedOnBackground(data.backgroundColor);
                    }

                    return div;
                }
            }
        });

        this.previousTags = this.tomSelect.getValue().slice();
    }

    itemCreate(input) {
        const color = randomColor();

        return {
            value:input,
            text:input,
            backgroundColor: color.formatHex()
        }
    }

    optionAdd(tag, data) {
        const formData = new FormData;
        formData.append('name', tag);
        formData.append('backgroundColor', data.backgroundColor);

        return post(this.createUrlValue, formData);
    }

    edit() {
        this.showEdit();
    }

    async save() {
        this.hideEdit();

        const selectedItems = this.tomSelect.getValue();

        if (isArrayEqual(this.previousTags, selectedItems)) {
            return Promise.resolve();
        }

        this.updateTags();

        const formData = new FormData();
        formData.append('tags', selectedItems);

        this.previousTags = selectedItems.slice();

        await post(this.urlValue, formData);

        this.renderEvents();
    }

    updateTags() {
        this.listTarget.innerHTML = '';

        const selectedValues = this.getSelectedValues();

        if (selectedValues.length === 0) {
            this.showEmptyText();
        } else {
            this.hideEmptyText();
        }

        for (const selectedValue of selectedValues) {
            const newTag = this.tagTemplateTarget.content.cloneNode(true);

            newTag.firstElementChild.textContent = selectedValue.value;
            newTag.firstElementChild.setAttribute('data-background-text-background-color-value', selectedValue.backgroundColor);

            this.listTarget.append(newTag);
        }
    }

    getSelectedValues() {
        const selectedItems = this.tomSelect.getValue();

        const options = Object.values(this.tomSelect.options);

        return options.filter((option) => {
            return selectedItems.includes(option.value);
        });
    }

    showEmptyText() {
        this.emptyTextTarget.classList.remove('d-none');
    }

    hideEmptyText() {
        this.emptyTextTarget.classList.add('d-none');
    }

    hideEdit() {
        this.editButtonTarget.classList.remove('d-none');
        this.saveButtonTarget.classList.add('d-none');

        this.listTarget.classList.remove('d-none');
        this.selectContainerTarget.classList.add('d-none');
    }

    showEdit() {
        this.editButtonTarget.classList.add('d-none');
        this.saveButtonTarget.classList.remove('d-none');
        this.hideEmptyText();

        this.listTarget.classList.add('d-none');
        this.selectContainerTarget.classList.remove('d-none');
    }

    validateTag(input) {
        if (input.length > this.maxLengthValue) {
            return false;
        }

        const regex = new RegExp('^[A-Za-z_]+$');

        return regex.test(input);
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }
}
