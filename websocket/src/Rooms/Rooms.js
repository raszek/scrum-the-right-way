import {RoomMessage} from './RoomMessage.js';
import {Room} from './Room.js';

export class Rooms {

    constructor() {
        /**
         * @type {Map<string, Room>}
         */
        this.rooms = new Map();
    }

    join(roomUser, roomId, initIssue) {
        if (!this.rooms.has(roomId)) {
            this.rooms.set(roomId, new Room(initIssue));
        }

        const room = this.rooms.get(roomId);
        if (!room) {
            return;
        }

        room.broadcast(RoomMessage.joinMessage(roomUser.user));

        room.addUser(roomUser);

        roomUser.socket.send(RoomMessage.roomStateMessage(room.getUsers(), room.issue));
        room.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has joined room ${roomId}`));
    }

    leave(roomUser, roomId) {
        const room = this.rooms.get(roomId);
        if (!room) {
            return;
        }

        room.removeUser(roomUser);

        room.broadcast(RoomMessage.leaveMessage(roomUser.user));
        room.broadcast(RoomMessage.chatMessage(`${roomUser.user.fullName} has left room ${roomId}`));
    }

    handleMessage(roomUser, roomId, message) {
        const room = this.rooms.get(roomId);
        if (!room) {
            return;
        }

        switch (message.type) {
        case 'bet':
            room.makeBet(roomUser, message.data);
            break;
        case 'showBets':
            room.showBets(roomUser);
            break;
        case 'setStoryPoints':
            room.setStoryPoints(roomUser, message.data);
            break;
        case 'resetBets':
            room.resetBets(roomUser);
            break;
        case 'changeIssue':
            room.changeIssue(roomUser, message.data);
            break;
        case 'addIssue':
            room.addIssue(roomUser, message.data);
            break;
        case 'removeIssue':
            room.removeIssue(roomUser, message.data);
            break;
        }
    }
}
