import {Controller} from '@hotwired/stimulus';
import {Tab} from 'bootstrap';

export default class extends Controller {

    static targets = ['tab']

    connect() {
        for (const tabElement of this.tabTargets) {
            const tabTrigger = new Tab(tabElement);

            tabElement.addEventListener('click', (event) => {
                event.preventDefault()
                tabTrigger.show()
            });
        }
    }

}
