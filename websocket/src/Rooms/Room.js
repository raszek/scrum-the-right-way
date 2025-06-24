import {RoomMessage} from './RoomMessage.js';

export class Room {

    constructor() {
        this.users = new Set();
    }

    broadcast(message) {
        for (const roomUser of this.users.values()) {
            roomUser.socket.send(message);
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

    emit(currentRoomUser, message) {
        for (const roomUser of this.users.values()) {
            if (roomUser !== currentRoomUser) {
                roomUser.socket.send(message);
            }
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
        this.broadcast(RoomMessage.showBetsMessages(this.getUsersBets()));
    }
}
