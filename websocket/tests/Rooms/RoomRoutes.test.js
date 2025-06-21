'use strict';

import { test } from 'node:test';
import FastifyWebsocket from '@fastify/websocket';

import {FastifyTest} from '../../src/App.js';
import {timeout} from '../../src/Util/Promise.js';
import Clock from '../../src/Date/Clock.js';
import Authentication from '../../src/Auth/Authentication.js';
import RoomRoutes from '../../src/Rooms/RoomRoute.js';

test('User can connect to server', async (t) => {
    t.plan(1);

    const fastify = FastifyTest();

    class FakeBackend {
        async checkRoomAccess(projectId, roomId, token) {
            if (projectId !== 'projectid') {
                return Promise.reject(new Error('Invalid project id'));
            }

            if (roomId !== 'some-room-id') {
                return Promise.reject(new Error('Invalid room id'));
            }

            if (!token) {
                return Promise.reject(new Error('No token provided'));
            }

            return Promise.resolve();
        }
    }

    fastify.register(Clock);
    fastify.register(Authentication);
    fastify.decorate('backendApi', new FakeBackend());
    fastify.register(FastifyWebsocket);
    fastify.register(RoomRoutes);

    t.after(() => fastify.close());

    await fastify.listen({ port: 0 });

    const jwtOptions = {
        exp: Math.floor(Date.now() / 1000) + (60 * 60),
        id: '1234567890',
        fullName: 'John Smith',
    };

    const jwtToken = fastify.jwtService.encode(jwtOptions);

    const port = fastify.server.address().port;

    const ws = new WebSocket(`ws://localhost:${port}/projects/projectid/rooms/some-room-id?token=${jwtToken}`);

    let resolve;
    const promise = new Promise((r) => {
        resolve = r;
    });

    ws.onmessage = (event) => {
        resolve(event.data);
    };

    const result = await timeout(promise);

    t.assert.deepStrictEqual(result, '{"type":"roomState","users":[{"id":"1234567890","fullName":"John Smith"}]}');

    ws.close();
});
