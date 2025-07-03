import {RoomMessage} from './RoomMessage.js';

export class Room {

    constructor(issue) {
        this.users = new Set();

        this.issue = issue;
    }

    broadcast(message) {
        for (const roomUser of this.users.values()) {
            roomUser.socket.send(message);
        }
    }

    emit(loggedRoomUser, message) {
        for (const roomUser of this.users.values()) {
            if (loggedRoomUser.user.id !== roomUser.user.id) {
                roomUser.socket.send(message);
            }
        }
    }

    getUsers() {
        const users = [];

        for (const roomUser of this.users.values()) {
            users.push(roomUser.get());
        }

        return users;
    }

    getUsersBets() {
        return this.getUsers().filter(user => user.bet !== undefined);
    }

    removeBets() {
        for (const roomUser of this.users.values()) {
            roomUser.bet = undefined;
        }
    }

    removeUser(roomUser) {
        this.users.delete(roomUser);
    }

    addUser(roomUser) {
        this.users.add(roomUser);
    }

    makeBet(roomUser, bet) {
        roomUser.makeBet(bet);

        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has made a bet.`));
        this.broadcast(RoomMessage.betMessage(roomUser.get()));
    }

    showBets(roomUser) {
        for (const roomUser of this.users.values()) {
            roomUser.showBet();
        }

        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has shown all bets.`));
        this.broadcast(RoomMessage.showBetsMessage(this.getUsersBets()));
    }

    changeIssue(roomUser, issue) {
        this.issue = issue;
        this.removeBets();

        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has changed issue to ${issue.id}.`));
        this.broadcast(RoomMessage.changeIssueMessage(issue));
    }

    addIssue(roomUser, issue) {
        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has added new issue ${issue.value}.`));
        this.emit(roomUser, RoomMessage.addIssueMessage(issue));
    }

    removeIssue(roomUser, issueId) {
        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has removed issue ${issueId}.`));
        this.broadcast(RoomMessage.removeIssueMessage(issueId));
    }

    setStoryPoints(roomUser, storyPoints) {
        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has set story points to ${storyPoints}.`));
        this.broadcast(RoomMessage.setStoryPointsMessage(storyPoints));
    }

    resetBets(roomUser) {
        this.removeBets();

        this.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has reset bets.`));
        this.broadcast(RoomMessage.resetBets());
    }
}
