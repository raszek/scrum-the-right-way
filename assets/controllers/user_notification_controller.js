import {Controller} from '@hotwired/stimulus';
import {post} from 'util';

export default class extends Controller {

    static values = {
        markAllReadUrl: String
    };

    static targets = ['item'];

    connect() {
        this.lineThroughReadItems();
    }

    lineThroughReadItems() {
        for (const item of this.itemTargets) {
            this.renderItem(item);
        }
    }

    markAllAsRead() {
        for (const item of this.itemTargets) {
            this.markAsRead(item);
        }

        return post(this.markAllReadUrlValue);
    }

    markAsRead(item) {
        if (this.isItemRead(item)) {
            return;
        }

        item.setAttribute('data-user-notification-is-read-param', 'true');

        this.renderItem(item);
    }

    isItemRead(item) {
        return item.getAttribute('data-user-notification-is-read-param') === 'true'
    }

    renderItem(item) {
        if (this.isItemRead(item)) {
            item.classList.add('text-decoration-line-through');
        } else {
            item.classList.remove('text-decoration-line-through');
        }
    }

    toggle(e) {
        const item = e.currentTarget.parentElement;

        if (this.isItemRead(item)) {
            post(e.params.markUnreadUrl);
            item.setAttribute('data-user-notification-is-read-param', 'false');
        } else {
            post(e.params.markReadUrl);
            item.setAttribute('data-user-notification-is-read-param', 'true');
        }

        this.renderItem(item);
    }
}
