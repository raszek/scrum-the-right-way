export class RoomUser {

    /**
     * @param {WebSocket} socket
     * @param {id: string, fullName: string} user
     */
    constructor(socket, user) {
        this.socket = socket;
        this.user = user;
    }
}
