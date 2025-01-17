import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['reader', 'writer'];

    rewriteCode() {
        if (this.readerTarget.value.length > 3) {
            return;
        }

        this.writerTarget.value = this.readerTarget.value.toUpperCase();
    }

    upperCase() {
        this.writerTarget.value = this.writerTarget.value.toUpperCase();
    }
}
