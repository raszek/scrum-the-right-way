import {Controller} from '@hotwired/stimulus';
import {get} from 'util';

export default class extends Controller {

    static values = {
        url: String,
        token: String
    }

    static targets = [
        'userTemplate',
        'userContainer',
        'user',
        'betButton',
        'issue',
        'issueContent',
        'issueLoader'
    ];

    connect() {
        this.socket = new WebSocket(this.urlValue);

        this.socket.addEventListener('message', this.messageHandler.bind(this));
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
        }
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

        this.resetBets();

        this.activateIssue(issueElement);

        const issueUrl = issueElement.getAttribute('data-room--poker-url-param');

        this.issueLoaderTarget.classList.remove('d-none');
        this.issueContentTarget.innerHTML = '';
        this.issueContentTarget.innerHTML = await get(issueUrl);
        this.issueLoaderTarget.classList.add('d-none');
    }

    changeIssue(event) {
        this.activateIssue(event.currentTarget);

        const issue = {
            id: event.params.id,
            storyPoints: event.params.storyPoints || undefined,
        };

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
        }
    }

    makeBetSelected(betElement) {
        betElement.innerHTML = '<i class="bi bi-exclamation-circle"></i>';
    }

    makeBetEmpty(betElement) {
        betElement.innerHTML = '<i class="bi bi-question-circle"></i>';
    }

    resetBets() {
        const betElements = document.querySelectorAll('.strw-poker-bet');

        for (const betElement of betElements) {
            this.makeBetEmpty(betElement);
        }

        this.removeSelectedBet();
    }
}
