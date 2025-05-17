import Dialog from '@stimulus-components/dialog';

export default class extends Dialog {

    static targets = ['form'];

    static values = {
        attributeName: String
    }

    open(event) {
        super.open();

        event.stopPropagation();

        const actionAttributeName = this.attributeNameValue || 'action';

        this.formTarget.setAttribute(actionAttributeName, event.params.url);
    }

}
