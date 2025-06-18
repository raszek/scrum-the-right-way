import Fastify from 'fastify';
import FastifyWebsocket from '@fastify/websocket';
import RoomRoutes from './Rooms/RoomRoute.js';
import FastifyEnv from '@fastify/env';
import {envOptions} from './Config/Env.js';
import Authentication from './Auth/Authentication.js';
import Clock from './Date/Clock.js';

const defaultAppConfig = {
    logger: true,
    showError: false
};

const testConfig = {
    logger: false,
    showError: true
};

export const configFastify = (config) => {
    const app = Fastify(config);
    app.register(FastifyEnv, envOptions);

    return app;
};

const build = (config = defaultAppConfig) => {
    const app = configFastify(config);

    app.register(Clock);
    app.register(Authentication);
    app.register(FastifyWebsocket);
    app.register(RoomRoutes);

    return app;
};

export const TestFastify = () => {
    return build(testConfig);
};

export default build;
