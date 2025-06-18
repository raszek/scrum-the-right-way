import {Rooms} from './Rooms.js';
import {RoomUser} from './RoomUser.js';

const rooms = new Rooms();

const routes = function (fastify, _, done) {

    fastify.addHook('preValidation', async (request, reply) => {

        try {
            await request.isAuthenticated();
        } catch (e) {
            return reply.code(401).send(e.message);
        }
    });

    fastify.get('/rooms/:roomId', { websocket: true }, async (socket, request) => {
        socket.on('error', (error) => {
            request.log.error(error);
        });

        const {roomId} = request.params;

        request.log.info(`User connected to room ${roomId}`);

        const roomUser = new RoomUser(socket, request.user);

        rooms.join(roomUser, roomId);

        socket.on('close', () => {
            rooms.leave(roomUser, roomId);
        });
    });

    done();
};

export default routes;
