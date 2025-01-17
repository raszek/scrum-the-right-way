import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['item', 'button'];

    connect() {
        super.connect();

        this.isVisible = false;

        this.display();
    }

    toggle() {
        this.isVisible = !this.isVisible;

        this.display();
    }

    display() {
        if (this.isVisible) {
            this.itemTarget.classList.remove('d-none');
            this.buttonTarget.innerHTML = '<i class="bi bi-eye-slash"></i>';
        } else {
            this.itemTarget.classList.add('d-none');
            this.buttonTarget.innerHTML = '<i class="bi bi-eye"></i>';
        }
    }

}
