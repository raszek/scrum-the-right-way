import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';

export default class extends Controller {

    static targets = ['modal'];

    connect() {
        this.DEFAULT_MODAL_ID = 'default';
        this.modals = {};
        for (const modalTarget of this.modalTargets) {
            const modalIdentifier = modalTarget.getAttribute('data-confirm-modal-id-param') || this.DEFAULT_MODAL_ID;
            this.modals[modalIdentifier] = new Modal(modalTarget);
        }
    }

    open(event) {
        const modalIdentifier = event.params.id || this.DEFAULT_MODAL_ID;

        const modal = this.modals[modalIdentifier];
        if (!modal) {
            throw new Error(`No modal found with identifier "${modalIdentifier}".`)
        }

        const modalTarget = this.findModalTarget(modalIdentifier);
        if (!modalTarget) {
            throw new Error(`No modal target found with identifier "${modalIdentifier}".`)
        }

        const form = modalTarget.querySelector('form');
        if (!form) {
            throw new Error('Form in modal not found');
        }

        if (!event.params.url) {
            throw new Error('Url for form not found');
        }

        form.setAttribute('action', event.params.url);

        modal.show();
    }

    findModalTarget(modalId) {
        for (const modalTarget of this.modalTargets) {
            if (modalTarget.getAttribute('data-confirm-modal-id-param') === modalId) {
                return modalTarget;
            }
        }

        return undefined;
    }

}
