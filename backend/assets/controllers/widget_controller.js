import { Controller } from '@hotwired/stimulus';
import {post, isEmpty} from 'util';

export default class WidgetController extends Controller {

    static values = {
        url: String,
        attributes: Object
    }

    attributesValueChanged(value, previousValue) {
        if (isEmpty(previousValue)) {
            return;
        }

        this.render(value);
    }

    async render (attributes) {

        const json = JSON.stringify(attributes);

        const formData = new FormData();

        formData.append('state', json);

        this.element.outerHTML = await post(this.urlValue, formData);
    }
}
