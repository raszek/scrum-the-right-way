const routes = function (fastify, _, done) {
    fastify.get('/rooms/:roomId', { websocket: true }, (socket, request) => {
        request.log.info(`User connected to room ${request.params.roomId}`);

        socket.send('hi from server');
        socket.on('message', () => {
            socket.send('answer from server');
        });
    });

    done();
}

export default routes;
