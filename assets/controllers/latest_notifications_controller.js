import { Controller } from '@hotwired/stimulus';
import {Popover} from 'bootstrap';
import {get} from 'util';

export default class extends Controller {

    static values = {
        fetchUrl: String
    };

    static targets = ['content', 'button', 'notificationCount'];

    connect() {
        const allowList = Popover.Default.allowList;

        allowList.a = [
            'data-action',
            'href'
        ];

        allowList.div = [
            'data-controller',
            'data-latest-notifications-inner-url-value',
            'data-latest-notifications-inner-target',
            'data-latest-notifications-inner-latest-notifications-outlet'
        ];

        this.popover = new Popover(this.buttonTarget, {
            content: this.contentTarget.innerHTML,
            placement: 'bottom',
            html: true,
            allowList
        });
    }

    async ajaxShow() {
        this.popover.show();

        if (!this.fetchUrlValue) {
            return Promise.resolve();
        }

        this.popover.setContent({
            '.popover-body': this.contentTarget.innerHTML
        });

        let content;
        try {
            content = await get(this.fetchUrlValue);
        } catch (e) {
            content = 'Internal server error';
        }

        this.popover.setContent({
            '.popover-body': content
        });
    }

    hideNotificationCount() {
        this.notificationCountTarget.classList.add('d-none');
    }
}
