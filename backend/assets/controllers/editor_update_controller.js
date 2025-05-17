import { Controller } from '@hotwired/stimulus';
import Editor from '@toast-ui/editor';

export default class extends Controller {

    static targets = ['editButtons', 'viewButtons', 'editor', 'viewer'];

    static outlets = ['issue-events'];

    static values = {
        url: String,
    }

    initialize() {
        this.currentValue = this.viewerTarget.innerText;
        this.viewer = Editor.factory({
            el: this.viewerTarget,
            viewer: true,
            initialValue: this.currentValue,
        });
    }

    cancel() {
        this.editButtonsTarget.style.display = 'none';
        this.viewButtonsTarget.style.display = 'flex';

        this.editorTarget.style.display = 'none';
        this.viewerTarget.style.display = 'block';
    }

    edit() {
        this.editButtonsTarget.style.display = 'flex';
        this.viewButtonsTarget.style.display = 'none';

        this.editorTarget.style.display = 'block';
        this.viewerTarget.style.display = 'none';
        this.createEditor();
    }

    async save() {
        if (!this.editor) {
            return;
        }

        const markdown = this.editor.getMarkdown();

        this.currentValue = markdown;

        this.viewer = Editor.factory({
            el: this.viewerTarget,
            viewer: true,
            initialValue: markdown,
        });

        this.cancel();

        const formData = new FormData();
        formData.append('description', markdown);

        await fetch(this.urlValue, {
            method: 'POST',
            body: formData
        })

        await this.renderEvents();
    }

    createEditor() {
        this.editor = new Editor({
            el: this.editorTarget,
            height: 'auto',
            initialEditType: 'markdown',
            previewStyle: 'tab',
            autofocus: false,
            initialValue: this.currentValue
        });
    }

    renderEvents() {
        if (!this.issueEventsOutlet) {
            return;
        }

        this.issueEventsOutlet.render();
    }
}
