export class RoomUser {

    /**
     * @param {WebSocket} socket
     * @param {id: string, fullName: string} user
     */
    constructor(socket, user) {
        this.socket = socket;
        this.user = user;
        this.bet = undefined;
    }

    makeBet(value) {
        this.bet = {
            type: 'hidden',
            value,
        };
    }

    showBet() {
        if (this.bet === undefined) {
            return;
        }

        this.bet.type = 'visible';
    }

    getBet() {
        if (this.bet === undefined) {
            return undefined;
        }

        if (this.bet.type === 'hidden') {
            return {
                type: 'hidden',
            };
        }

        return this.bet;
    }

    get() {
        return {
            ...this.user,
            bet: this.getBet(),
        };
    }
}
