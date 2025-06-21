import FastifyPlugin from 'fastify-plugin';

class BackendApi {

    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    async checkRoomAccess(projectId, roomId, token) {
        const url = `${this.baseUrl}/projects/${projectId}/rooms/${roomId}/access`;

        const response = await fetch(url, {
            method: 'GET',
            headers: new Headers({
                'Authorization': `Bearer ${token}`,
            })
        });

        if (response.status === 204) {
            return Promise.resolve();
        }

        return Promise.reject(new Error('Cannot access room'));
    }

}

function backendPlugin(fastify, _, done) {
    const backendApi = new BackendApi(
        fastify.config.BACKEND_HOST
    );

    fastify.decorate('backendApi', backendApi);

    done();
}

export default FastifyPlugin(backendPlugin);
