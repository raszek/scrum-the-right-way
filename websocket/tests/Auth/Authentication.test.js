import {test} from 'node:test';
import Authentication from '../../src/Auth/Authentication.js';
import {ImmutableDateTime} from '../../src/Date/ImmutableDateTime.js';
import {FastifyTest} from '../../src/App.js';

test('JWT token can expire', async (t) => {
    t.plan(1);

    const fastify = FastifyTest();

    const currentTime = ImmutableDateTime.create(2012, 12, 12, 12, 12);

    fastify.decorate('clock', {
        currentTimestamp: () => currentTime.timestamp(),
    });

    fastify.register(Authentication);

    await fastify.ready();

    const twoHoursAgo = currentTime.subHours(2);

    const jwtOptions = {
        exp: twoHoursAgo.timestamp(),
        id: '1234567890',
        fullName: 'John Smith',
    };

    const token = fastify.jwtService.encode(jwtOptions);

    let error = undefined;
    try {
        fastify.jwtService.decode(token);
    } catch (e) {
        error = e;
    }

    t.assert.strictEqual(error.message, 'jwt expired');
});
