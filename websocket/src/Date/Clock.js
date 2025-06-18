import FastifyPlugin from 'fastify-plugin';
import {ImmutableDateTime} from './ImmutableDateTime.js';

class Clock {

    now() {
        return ImmutableDateTime.now();
    }

    currentTimestamp() {
        return this.now().timestamp();
    }
}

function clockPlugin(fastify, _, done) {
    fastify.decorate('clock', new Clock());

    done();
}

export default FastifyPlugin(clockPlugin);
