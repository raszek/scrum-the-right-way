import {Controller} from '@hotwired/stimulus';
import {Popover} from 'bootstrap';
import {post} from 'util';

export default class extends Controller {

    static values = {
        title: String,
        url: String,
        current: String
    };

    static targets = ['button', 'template'];

    connect() {
        this.popover = new Popover(this.buttonTarget, {
            title: this.titleValue,
            html: true,
            placement: 'bottom',
            allowList: this.getAllowList(),
        });

        this.buttonTarget.addEventListener('show.bs.popover', this.onShow.bind(this));
    }

    onShow() {
        const clone = this.templateTarget.content.cloneNode(true);

        const button = clone.querySelector('button');

        if (!button) {
            throw new Error('Button not found');
        }

        const input = clone.querySelector('input');

        if (!input) {
            throw new Error('Input not found');
        }

        input.value = this.currentValue;

        button.addEventListener('click', this.save(input).bind(this));

        this.popover.setContent({
            '.popover-header': this.titleValue,
            '.popover-body': clone,
        });
    }

    formatValue(value) {
        return value;
    }

    save(input) {
        const that = this;

        return async function () {
            const value = input.value;

            that.buttonTarget.innerText = that.formatValue(value);

            that.currentValue = value;

            that.popover.hide();

            await that.request(value);

            that.afterRequestAction(value);
        };
    }

    request(value) {
        const formData = new FormData();

        formData.append('value', value);

        return post(this.urlValue, formData);
    }

    afterRequestAction(value) {

    }

    getAllowList() {
        const defaultAllowList = Popover.Default.allowList;

        defaultAllowList.input = [
            'type',
            'data-editable-target',
            'min',
            'value',
        ];

        defaultAllowList.button = [
            'data-action',
        ];

        return defaultAllowList;
    }
}
