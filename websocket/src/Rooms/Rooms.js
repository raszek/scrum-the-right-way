import {RoomMessage} from './RoomMessage.js';

export class Rooms {

    constructor() {
        /**
         * @type {Map<string, Set<RoomUser>>}
         */
        this.rooms = new Map();
    }

    join(roomUser, roomId) {
        if (!this.rooms.has(roomId)) {
            this.rooms.set(roomId, new Set());
        }

        const room = this.rooms.get(roomId);

        if (!room) {
            return;
        }

        this.broadcast(room, RoomMessage.joinMessage(roomUser.user));

        room.add(roomUser);

        roomUser.socket.send(RoomMessage.roomStateMessage(this.roomState(room)));
        this.broadcast(room, RoomMessage.chatMessage(`${roomUser.user.fullName} has joined room ${roomId}`));
    }

    leave(roomUser, roomId) {
        const room = this.rooms.get(roomId);

        if (!room) {
            return;
        }


        room.delete(roomUser);

        this.broadcast(room, RoomMessage.leaveMessage(roomUser.user));
        this.broadcast(room, RoomMessage.chatMessage(`${roomUser.user.fullName} has left room ${roomId}`));
    }

    broadcast(room, message) {
        for (const roomUser of room) {
            roomUser.socket.send(message);
        }
    }

    roomState(room) {
        const users = [];

        for (const roomUser of room.values()) {
            users.push(roomUser.user);
        }

        return users;
    }
}
