export class RoomMessage {

    static chatMessage(message) {
        return RoomMessage.message({
            type: 'chat',
            data: message,
        });
    }

    static joinMessage(user) {
        return RoomMessage.message({
            type: 'join',
            data: user,
        });
    }

    static leaveMessage(user) {
        return RoomMessage.message({
            type: 'leave',
            data: user,
        });
    }

    static removeMessage(user) {
        return RoomMessage.message({
            type: 'remove',
            data: user,
        });
    }

    static roomStateMessage(users, issue) {
        return RoomMessage.message({
            type: 'roomState',
            data: {
                users,
                issue,
            },
        });
    }

    static betMessage(user) {
        return RoomMessage.message({
            type: 'bet',
            data: user
        });
    }

    static showBetsMessage(users) {
        return RoomMessage.message({
            type: 'showBets',
            data: users
        });
    }

    static changeIssueMessage(issueId) {
        return RoomMessage.message({
            type: 'changeIssue',
            data: issueId
        });
    }

    static addIssueMessage(issue) {
        return RoomMessage.message({
            type: 'addIssue',
            data: issue
        });
    }

    static removeIssueMessage(issueId) {
        return RoomMessage.message({
            type: 'removeIssue',
            data: issueId
        });
    }

    static setStoryPointsMessage(storyPoints) {
        return RoomMessage.message({
            type: 'setStoryPoints',
            data: storyPoints
        });
    }

    static resetBets() {
        return RoomMessage.message({
            type: 'resetBets',
        });
    }

    static message(data) {
        return JSON.stringify(data);
    }
}
