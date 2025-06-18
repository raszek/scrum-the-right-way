import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

    static values = {
        url: String,
        token: String
    }

    static targets = ['userTemplate', 'userContainer', 'user'];

    connect() {
        const socket = new WebSocket(this.urlValue);

        socket.addEventListener('message', this.messageHandler.bind(this));
    }

    messageHandler(event) {
        const message = JSON.parse(event.data);

        switch (message.type) {
            case 'chat':
                this.chatMessage(message.message);
                break;
            case 'join':
                this.addUser(message.user);
                break;
            case 'leave':
                this.removeUser(message.user);
                break;
            case 'roomState':
                this.setUsers(message.users);
                break;

        }
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
}
