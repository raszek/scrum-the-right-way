import FastifyPlugin from 'fastify-plugin';
import {AuthError} from './AuthError.js';
import {JwtService} from './JwtService.js';

const auth = FastifyPlugin( (fastify, _, done) => {
    fastify.decorate('jwtService', new JwtService(fastify.config.JWT_SECRET, fastify.clock));

    fastify.decorateRequest('isAuthenticated', function () {
        const token = this.query.token;

        if (!token) {
            throw new AuthError('No "token" value in query');
        }

        let decoded;
        try {
            decoded = fastify.jwtService.decode(token, fastify.config.JWT_SECRET);
        } catch (e) {
            throw new AuthError(e.message);
        }

        if (!decoded.id) {
            throw new AuthError('Token must have encoded "id" field');
        }

        if (!decoded.fullName) {
            throw new AuthError('Token must have encoded "fullName" field');
        }

        this.token = token;
        this.user = {
            id: decoded.id,
            fullName: decoded.fullName,
        };
    });

    done();
});

export default auth;
