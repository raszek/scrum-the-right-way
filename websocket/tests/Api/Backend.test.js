import {test} from 'node:test';

import {FastifyTest} from '../../src/App.js';
import Backend from '../../src/Api/Backend.js';
import Authentication from '../../src/Auth/Authentication.js';

test('Can check if room is accessible', async (t) => {
    t.plan(1);

    const fastify = FastifyTest();

    fastify.register(Authentication);
    fastify.register(Backend);

    await fastify.ready();

    const jwtOptions = {
        exp: Math.floor(Date.now() / 1000) + (60 * 60),
        id: 'UkLWZg9DAJ',
        fullName: 'John Smith',
    };

    const jwtToken = fastify.jwtService.encode(jwtOptions);

    const response = await fastify.backendApi.checkRoomAccess('UkLWZg9DAJ', 'gbHJdmfrXB', jwtToken);

    t.assert.strictEqual(response, undefined);
});
