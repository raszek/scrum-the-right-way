export class RoomMessage {

    static chatMessage(message) {
        return RoomMessage.message({
            type: 'chat',
            message,
        });
    }

    static joinMessage(user) {
        return RoomMessage.message({
            type: 'join',
            user,
        });
    }

    static leaveMessage(user) {
        return RoomMessage.message({
            type: 'leave',
            user,
        });
    }

    static roomStateMessage(users) {
        return RoomMessage.message({
            type: 'roomState',
            users,
        });
    }

    static message(data) {
        return JSON.stringify(data);
    }
}
