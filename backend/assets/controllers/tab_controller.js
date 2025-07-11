import {Controller} from '@hotwired/stimulus';
import {Tab} from 'bootstrap';

export default class extends Controller {

    static targets = ['tab']

    connect() {
        for (const tabElement of this.tabTargets) {
            const tabTrigger = new Tab(tabElement);

            tabElement.addEventListener('click', (event) => {
                event.preventDefault();
                tabTrigger.show();
            });

            if (tabElement.classList.contains('active')) {
                tabTrigger.show();
                const tabContent = document.querySelector(tabElement.getAttribute('data-bs-target'));
                tabContent.classList.add('active');
            }
        }
    }

}
