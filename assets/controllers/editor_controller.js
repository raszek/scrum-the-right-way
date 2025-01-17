import { Controller } from '@hotwired/stimulus';
import Editor from '@toast-ui/editor';

export default class extends Controller {

    static targets = ['container', 'hidden'];

    initialize() {
        this.editor = new Editor({
            el: this.containerTarget,
            height: '500px',
            initialEditType: 'markdown',
            previewStyle: 'tab',
            autofocus: false
        });
    }

    submitForm(e) {
        e.preventDefault();

        if (!this.editor) {
            return;
        }

        this.hiddenTarget.value = this.editor.getMarkdown();

        e.target.submit();
    }
}
