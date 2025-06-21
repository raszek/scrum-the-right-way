const envSchema = {
    type: 'object',
    required: ['JWT_SECRET'],
    properties: {
        APP_ENV: {
            type: 'string',
        },
        JWT_SECRET: {
            type: 'string',
        },
        BACKEND_HOST: {
            type: 'string',
        },
    }
};

export const envOptions = {
    schema: envSchema,
    dotenv: true,
};


