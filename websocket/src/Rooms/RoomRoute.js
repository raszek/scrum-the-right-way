import {Rooms} from './Rooms.js';
import {RoomUser} from './RoomUser.js';

const rooms = new Rooms();

const routes = function (fastify, _, done) {

    fastify.addHook('preValidation', async (request, reply) => {

        try {
            await request.isAuthenticated();
        } catch (e) {
            request.log.info(e.message);
            return reply.code(401).send(e.message);
        }

        const {projectId, roomId} = request.params;

        try {
            fastify.initIssue = await fastify.backendApi.checkRoomAccess(projectId, roomId, request.token);
        } catch (e) {
            request.log.info(e.message);
            return reply.code(403).send(e.message);
        }
    });

    fastify.get('/projects/:projectId/rooms/:roomId', { websocket: true }, async (socket, request) => {
        socket.on('error', (error) => {
            request.log.error(error);
        });

        const {roomId} = request.params;

        request.log.info(`User connected to room ${roomId}`);

        const roomUserAlreadyExist = rooms.findRoomUser(request.user.id, roomId);
        if (roomUserAlreadyExist) {
            rooms.remove(roomUserAlreadyExist, roomId);
        }

        const roomUser = new RoomUser(socket, request.user);

        rooms.join(roomUser, roomId, fastify.initIssue);

        socket.on('message', (message) => {
            request.log.info(`Got message: ${message.toString()}`);
            rooms.handleMessage(roomUser, roomId, JSON.parse(message.toString()));
        });

        socket.on('close', () => {
            request.log.info(`User left room ${roomId}`);
            rooms.leave(roomUser, roomId);
        });
    });

    done();
};

export default routes;
