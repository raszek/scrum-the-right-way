import Fastify from 'fastify';
import FastifyWebsocket from '@fastify/websocket';
import RoomRoutes from './Rooms/RoomRoute.js';
import FastifyEnv from '@fastify/env';
import {envOptions} from './Config/Env.js';
import Authentication from './Auth/Authentication.js';
import Clock from './Date/Clock.js';
import Backend from './Api/Backend.js';

const defaultAppConfig = {
    logger: true,
    showError: false
};

const testConfig = {
    logger: false,
    showError: true
};

const fastifyCore = (config) => {
    const app = Fastify(config);
    app.register(FastifyEnv, envOptions);

    return app;
};

export const FastifyTest = () => {
    return fastifyCore(testConfig);
};

const build = (config = defaultAppConfig) => {
    const app = fastifyCore(config);

    app.register(Clock);
    app.register(Authentication);
    app.register(Backend);
    app.register(FastifyWebsocket);
    app.register(RoomRoutes);

    return app;
};

export default build;
