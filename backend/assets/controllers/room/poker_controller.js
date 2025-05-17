import {Controller} from '@hotwired/stimulus';

export default class extends Controller {


    connect() {
        const socket = new WebSocket('ws://localhost:8000/websocket/rooms/some-room-id');

        socket.addEventListener('open', (event) => {
            console.log('dziala');
        });
    }
}
