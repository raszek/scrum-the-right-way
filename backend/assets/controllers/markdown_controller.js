import { Controller } from '@hotwired/stimulus';
import Editor from '@toast-ui/editor';

export default class extends Controller {

    static targets = ['text'];

    initialize() {
        Editor.factory({
            el: this.element,
            viewer: true,
            initialValue: this.element.innerText,
        });
    }
}
