import { Controller } from '@hotwired/stimulus';
import {post} from 'util';

export default class extends Controller {

    static values = {
        url: String
    };

    static targets = ['container', 'button'];

    static outlets = ['latest-notifications'];

    markAllRead() {
        this.containerTarget.innerHTML = '<b>No unread notifications</b>';

        this.buttonTarget.classList.add('d-none');

        this.latestNotificationsOutlet.hideNotificationCount();

        return post(this.urlValue);
    }
}
