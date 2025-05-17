import { Controller } from '@hotwired/stimulus';
import {post} from 'util';

export default class extends Controller {

    static values = {
        memberId: String,
        memberFullName: String,
        observeUrl: String,
        unobserveUrl: String,
    }

    static targets = [
        'item',
        'observeButton',
        'unobserveButton',
        'container',
        'emptyText'
    ];

    connect() {
        this.renderObserveButton();
        this.renderEmptyText();
    }

    observe() {
        const member = {
            id: this.memberIdValue,
            fullName: this.memberFullNameValue
        };

        return this.addIssueObserver(member);
    }

    unobserve() {
        const li = this.findItem(this.memberIdValue);

        li.remove();

        this.renderObserveButton();
        this.renderEmptyText();

        return post(this.unobserveUrlValue);
    }

    addIssueObserverIfNotExist(member) {
        const item = this.findItem(member.id);

        if (item) {
            return Promise.resolve();
        }

        return this.addIssueObserver(member);
    }

    addIssueObserver(member) {
        const li = document.createElement('li');

        li.setAttribute('data-observer-target', 'item');
        li.setAttribute('data-observer-member-id-param', member.id);
        li.textContent = member.fullName;

        this.containerTarget.append(li);

        this.renderObserveButton();
        this.renderEmptyText();

        return post(this.observeUrlValue);
    }


    renderEmptyText() {
        if (this.itemTargets.length === 0) {
            this.emptyTextTarget.style.display = 'flex';
        } else {
            this.emptyTextTarget.style.display = 'none';
        }
    }

    renderObserveButton() {
        if (this.isLoggedInMemberObserving()) {
            this.showUnobserveButton();
        } else {
            this.showObserveButton();
        }
    }

    showObserveButton() {
        this.unobserveButtonTarget.style.display = 'none';
        this.observeButtonTarget.style.display = 'block';
    }

    showUnobserveButton() {
        this.unobserveButtonTarget.style.display = 'block';
        this.observeButtonTarget.style.display = 'none';
    }

    findItem(itemId) {
        for (const itemTarget of this.itemTargets) {
            if (itemTarget.getAttribute('data-observer-member-id-param') === itemId) {
                return itemTarget;
            }
        }

        return undefined;
    }

    isLoggedInMemberObserving() {
        return this.findItem(this.memberIdValue) !== undefined;
    }
}
