import {Controller} from '@hotwired/stimulus';
import {Popover} from 'bootstrap';
import {post} from 'util';

export default class extends Controller {

    static values = {
        title: String,
        url: String
    };

    static targets = ['button', 'template'];

    connect() {
        this.popover = new Popover(this.buttonTarget, {
            title: this.titleValue,
            html: true,
            placement: 'bottom',
            allowList: this.getAllowList()
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
            this.buttonTarget.innerText = that.formatValue(input.value);

            this.popover.hide();

            await that.request(input.value);

            that.afterRequestAction();
        };
    }

    request(value) {
        const formData = new FormData();

        formData.append('value', value);

        return post(this.urlValue, formData);
    }

    afterRequestAction() {

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
