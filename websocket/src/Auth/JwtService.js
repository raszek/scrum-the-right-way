import jwt from 'jsonwebtoken';

export class JwtService {

    /**
     * @param {string} secretKey
     * @param {Clock} clock
     */
    constructor(secretKey, clock) {
        this.clock = clock;
        this.secretKey = secretKey;
    }


    /**
     * @param {string} token
     * @return {object}
     */
    decode(token) {
        return jwt.verify(token, this.secretKey, {
            clockTimestamp: this.clock.currentTimestamp(),
        });
    }

    /**
     * @param {object} payload
     * @return {string}
     */
    encode(payload) {
        return jwt.sign(payload, this.secretKey);
    }
}
