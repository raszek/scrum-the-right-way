import {Controller} from '@hotwired/stimulus';
import {get, post} from 'util';
import TomSelect from 'tom-select';

export default class extends Controller {

    static values = {
        url: String,
        token: String,
        searchIssueUrl: String,
        addIssueUrl: String,
        setTabUrl: String,
    }

    static targets = [
        'userTemplate',
        'userContainer',
        'user',
        'betButton',
        'issue',
        'issueContent',
        'issueLoader',
        'issueSelect',
        'issueTemplate',
        'issueContainer',
        'storyPointSelect',
        'storyPointAverage',
    ];

    connect() {
        this.socket = new WebSocket(this.urlValue);

        this.socket.addEventListener('message', this.messageHandler.bind(this));

        this.addIssueSelect = new TomSelect(this.issueSelectTarget, {
            load: this.fetchRoomIssues.bind(this),
            onChange: this.addIssue.bind(this),
            render: {
                option: function (data, escape) {
                    return `
                    <div data-room--poker-story-point-param="${data.storyPoints}"
                         data-room--poker-url-param="${data.url}"
                         data-room--poker-remove-url-param="${data.removeUrl}"
                    >
                      ${escape(data.text)}
                    </div>
                    `;
                }
            }
        });
    }

    messageHandler(event) {
        const message = JSON.parse(event.data);
        console.log('Got message: ', message);

        switch (message.type) {
            case 'chat':
                this.chatMessage(message.data);
                break;
            case 'join':
                this.addUser(message.data);
                break;
            case 'leave':
                this.removeUser(message.data);
                break;
            case 'remove':
                this.userHasBeenKicked(message.data);
                break;
            case 'roomState':
                this.setRoomState(message.data);
                break;
            case 'bet':
                this.userMadeBet(message.data);
                break;
            case 'showBets':
                this.displayBets(message.data);
                break;
            case 'changeIssue':
                this.setCurrentIssue(message.data);
                break;
            case 'addIssue':
                this.userAddedIssue(message.data);
                break;
            case 'removeIssue':
                this.userRemovedIssue(message.data);
                break;
            case 'setStoryPoints':
                this.userChangedStoryPoints(message.data);
                break;
            case 'resetBets':
                this.userResetBets();
                break;
        }
    }

    async removeIssue(event) {
        event.preventDefault();

        const issueId = event.params.issueId;

        const issueElement = this.findIssueElement(issueId);
        if (!issueElement) {
            throw new Error('Issue element not found');
        }

        if (this.isActiveIssue(issueElement)) {
            const firstNonActiveIssue = this.findFirstNonActiveIssue();
            if (!firstNonActiveIssue) {
                throw new Error('Cannot remove every issue from room');
            }

            this.changeIssueAction(firstNonActiveIssue, {
                id: firstNonActiveIssue.getAttribute('data-room--poker-id-param'),
                storyPoints: firstNonActiveIssue.getAttribute('data-room--poker-story-points-param') || undefined,
            });
        }

        const removeUrl = issueElement.getAttribute('data-room--poker-remove-url-param');

        await post(removeUrl);

        this.sendMessage('removeIssue', issueId);
    }

    isActiveIssue(issueElement) {
        return issueElement.classList.contains('active');
    }

    async addIssue(value) {
        const option = this.addIssueSelect.getOption(value);
        if (!option) {
            return;
        }

        const clone = this.issueTemplateTarget.content.cloneNode(true);

        const storyPoints = option.getAttribute('data-room--poker-story-point-param') === 'null'
            ? null
            : option.getAttribute('data-room--poker-story-point-param');

        const url = option.getAttribute('data-room--poker-url-param');
        const removeUrl = option.getAttribute('data-room--poker-remove-url-param');
        const text = option.innerText.trim();

        const li = clone.firstElementChild;
        li.setAttribute('data-room--poker-id-param', value);
        li.setAttribute('data-room--poker-story-points-param', storyPoints);
        li.setAttribute('data-room--poker-url-param', url);
        li.setAttribute('data-room--poker-remove-url-param', removeUrl);

        const formData = new FormData();
        formData.append('issueId', value);

        this.issueContainerTarget.appendChild(li);

        this.addIssueSelect.clear();
        this.addIssueSelect.clearOptions();

        await post(this.addIssueUrlValue, formData);

        li.setAttribute('data-action', 'click->room--poker#changeIssue');

        const loader = li.querySelector('.strw-loader');
        loader.remove();
        const button = li.querySelector('button');
        button.setAttribute('data-confirm-modal-callback-value-param', value);
        button.classList.remove('d-none');

        this.changeIssueText(li, text);
        this.changeStoryPointText(li, storyPoints);

        this.updateRemoveIssueButtonState();

        const newIssue = {
            value,
            text,
            storyPoints,
            url,
            removeUrl
        };

        this.sendMessage('addIssue', newIssue);
    }

    userRemovedIssue(issueId) {
        const issueElement = this.findIssueElement(issueId);
        if (!issueElement) {
            throw new Error('Issue element not found');
        }

        issueElement.remove();

        this.updateRemoveIssueButtonState();
    }

    userAddedIssue(newIssue) {
        const clone = this.issueTemplateTarget.content.cloneNode(true);

        const li = clone.firstElementChild;
        li.setAttribute('data-room--poker-id-param', newIssue.value);
        li.setAttribute('data-room--poker-story-points-param', newIssue.storyPoints);
        li.setAttribute('data-room--poker-url-param', newIssue.url);
        li.setAttribute('data-room--poker-remove-url-param', newIssue.removeUrl);
        li.setAttribute('data-action', 'click->room--poker#changeIssue');

        const loader = li.querySelector('.strw-loader');
        loader.remove();
        const button = li.querySelector('button');
        button.setAttribute('data-confirm-modal-callback-value-param', newIssue.value);
        button.classList.remove('d-none');

        this.changeIssueText(li, newIssue.text);
        this.changeStoryPointText(li, newIssue.storyPoints);

        this.issueContainerTarget.appendChild(li);

        this.updateRemoveIssueButtonState();
    }

    async fetchRoomIssues(query, callback) {

        const encodedQuery = encodeURIComponent(query);

        const url = `${this.searchIssueUrlValue}?query=${encodedQuery}`;

        try {
            const data = JSON.parse(await get(url));

            callback(data);
        } catch (e) {
            callback();
        }
    }


    userChangedStoryPoints(storyPoints) {
        this.storyPointSelectTarget.value = storyPoints;

        const activeIssue = this.findActiveIssue();
        if (!activeIssue) {
            throw new Error('Active issue not found');
        }

        this.changeStoryPointText(activeIssue, storyPoints);
    }

    changeIssueText(li, text) {
        const p = li.querySelector('p');
        if (!p) {
            throw new Error('Issue text not found');
        }

        p.firstChild.nodeValue = text;
    }

    changeStoryPointText(li, storyPoints) {
        const span = li.querySelector('span.strw-issue-story-points');
        if (!span) {
            throw new Error('Story points span not found');
        }

        span.innerText = this.issueStoryPointText(storyPoints);
    }

    issueStoryPointText(storyPoints) {
        if (!storyPoints) {
            return '(none)';
        }

        return `(${storyPoints} story points)`;
    }

    async updateStoryPoints(event) {
        const value = event.currentTarget.value;

        const url = event.params.url;

        await this.storyPointRequest(value, url);

        this.sendMessage('setStoryPoints', value);
    }

    async storyPointRequest(storyPoints, url) {
        const formData = new FormData();
        if (storyPoints) {
            formData.append('points', storyPoints);
        }

        return fetch(url, {
            method: 'POST',
            body: formData
        })
    }

    setRoomState(data) {
        this.setCurrentIssue(data.issue);

        this.setUsers(data.users);
    }

    async setCurrentIssue(issue) {
        const issueElement = this.findIssueElement(issue.id);
        if (!issueElement) {
            throw new Error('Issue element not found');
        }

        this.removeBets();

        this.activateIssue(issueElement);

        const issueUrl = issueElement.getAttribute('data-room--poker-url-param');

        this.issueLoaderTarget.classList.remove('d-none');
        this.issueContentTarget.innerHTML = '';
        this.issueContentTarget.innerHTML = await get(issueUrl);
        this.issueLoaderTarget.classList.add('d-none');
    }

    changeIssue(event) {
        if (this.isActiveIssue(event.currentTarget)) {
            return;
        }

        const issue = {
            id: event.params.id,
            storyPoints: event.params.storyPoints || undefined,
        };

        this.changeIssueAction(event.currentTarget, issue);
    }

    changeIssueAction(element, issue) {
        this.activateIssue(element);

        this.sendMessage('changeIssue', issue);
    }

    activateIssue(issueElement) {
        for (const issueTarget of this.issueTargets) {
            issueTarget.classList.remove('active');
        }

        issueElement.classList.add('active');
    }

    findIssueElement(issueId) {
        for (const issueTarget of this.issueTargets) {
            if (issueTarget.getAttribute('data-room--poker-id-param') === issueId) {
                return issueTarget;
            }
        }
        return undefined;
    }

    findActiveIssue() {
        for (const issueTarget of this.issueTargets) {
            if (issueTarget.classList.contains('active')) {
                return issueTarget;
            }
        }

        return undefined;
    }

    findFirstNonActiveIssue() {
        for (const issueTarget of this.issueTargets) {
            if (!issueTarget.classList.contains('active')) {
                return issueTarget;
            }
        }

        return undefined;
    }

    userMadeBet(user) {
        const userElement = this.findUserElement(user);
        if (!userElement) {
            throw new Error('User element not found');
        }

        const betElement = userElement.querySelector('.strw-poker-bet');

        betElement.innerHTML = '<i class="bi bi-exclamation-circle"></i>';
    }

    bet(event) {
        const bet = event.params.value;

        this.removeSelectedBet();

        event.currentTarget.classList.add('active');

        this.sendMessage('bet', bet);
    }

    removeSelectedBet() {
        for (const betButton of this.betButtonTargets) {
            betButton.classList.remove('active');
        }
    }

    showBets() {
        this.sendMessage('showBets');
    }

    sendMessage(type, data) {
        this.socket.send(JSON.stringify({
            type,
            data
        }));
    }

    chatMessage(message) {
        console.log(message);
    }

    removeUser(user) {
        const userElement = this.findUserElement(user);

        if (!userElement) {
            throw new Error('User element not found');
        }

        userElement.remove();
    }

    addUser(user) {
        const clone = this.userTemplateTarget.content.cloneNode(true);

        clone.firstElementChild.setAttribute('data-room--poker-user-id-param', user.id);
        clone.firstElementChild.querySelector('.strw-poker-user-name').innerText = user.fullName;

        if (user.bet) {
            const pokerBetElement = clone.firstElementChild.querySelector('.strw-poker-bet');
            if (user.bet.type === 'hidden') {
                this.makeBetSelected(pokerBetElement);
            } else if (user.bet.type === 'visible') {
                pokerBetElement.innerHTML = user.bet.value;
            }
        }


        this.userContainerTarget.appendChild(clone);
    }

    setUsers(users) {
        this.userContainerTarget.innerHTML = '';

        for (const user of users) {
            this.addUser(user);
        }
    }

    findUserElement(user) {
        for (const userElement of this.userTargets) {
            if (userElement.getAttribute('data-room--poker-user-id-param') === user.id) {
                return userElement;
            }
        }

        return undefined;
    }

    displayBets(users) {
        let storyPointSum = 0;
        for (const user of users) {
            const userElement = this.findUserElement(user);
            if (!userElement) {
                throw new Error('User element not found');
            }

            const betElement = userElement.querySelector('.strw-poker-bet');
            if (!betElement) {
                throw new Error('Bet element not found');
            }

            betElement.innerHTML = user.bet.value;
            storyPointSum += user.bet.value;
        }

        if (users.length === 0) {
            throw new Error('No users found');
        }

        const average = storyPointSum / users.length;

        this.storyPointAverageTarget.innerText = `Average ${average} story points`;
    }

    makeBetSelected(betElement) {
        betElement.innerHTML = '<i class="bi bi-exclamation-circle"></i>';
    }

    makeBetEmpty(betElement) {
        betElement.innerHTML = '<i class="bi bi-question-circle"></i>';
    }

    removeBets() {
        const betElements = document.querySelectorAll('.strw-poker-bet');

        for (const betElement of betElements) {
            this.makeBetEmpty(betElement);
        }

        this.removeSelectedBet();
    }

    userResetBets() {
        this.removeBets()

        this.storyPointAverageTarget.innerText = '';
    }


    resetBets() {
        this.sendMessage('resetBets');
    }

    hideRemoveButtons(issueElements) {
        for (const issueElement of issueElements) {
            const removeButton = issueElement.querySelector('.strw-room-issue-remove-button');

            if (!removeButton) {
                throw new Error('Remove button not found');
            }

            removeButton.classList.add('d-none');
        }
    }

    showRemoveButtons(issueElements) {
        for (const issueElement of issueElements) {
            const removeButton = issueElement.querySelector('.strw-room-issue-remove-button');

            if (!removeButton) {
                throw new Error('Remove button not found');
            }

            removeButton.classList.remove('d-none');
        }
    }

    updateRemoveIssueButtonState() {
        if (this.issueTargets.length <= 1) {
            this.hideRemoveButtons(this.issueTargets);
        } else {
            this.showRemoveButtons(this.issueTargets);
        }
    }

    changeTab(event) {
        const tab = event.params.tab;

        const formData = new FormData();
        formData.append('tab', tab);

        return post(this.setTabUrlValue, formData);
    }

    userHasBeenKicked() {
        this.element.innerHTML = `<div class="col-12">You are removed from the room. Reason: Someone connected the room using your account</div>`;
    }
}
