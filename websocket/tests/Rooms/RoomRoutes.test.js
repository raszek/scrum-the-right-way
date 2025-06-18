'use strict';

import { test } from 'node:test';
import {TestFastify} from '../../src/App.js';
import {timeout} from '../../src/Util/Promise.js';

test('User can connect to server', async (t) => {
    t.plan(1);

    const fastify = TestFastify();

    t.after(() => fastify.close());

    await fastify.listen({ port: 0 });

    const jwtOptions = {
        exp: Math.floor(Date.now() / 1000) + (60 * 60),
        id: '1234567890',
        fullName: 'John Smith',
    };

    const jwtToken = fastify.jwtService.encode(jwtOptions);

    const port = fastify.server.address().port;

    const ws = new WebSocket(`ws://localhost:${port}/rooms/some-room-id?token=${jwtToken}`);

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
