import Fastify from 'fastify';
import FastifyWebsocket from '@fastify/websocket';
import RoomRoutes from './routes/room-routes.js';

const fastify = Fastify({
  logger: true
});

fastify.register(FastifyWebsocket);

fastify.get('/', async function handler () {
  return { message: 'It works!' }
});

fastify.register(RoomRoutes);

try {
    await fastify.listen({ port: 3000, host: '0.0.0.0' });
} catch (err) {
    fastify.log.error(err);
    process.exit(1);
}
