const envSchema = {
    type: 'object',
    required: ['JWT_SECRET'],
    properties: {
        JWT_SECRET: {
            type: 'string',
        }
    }
};

export const envOptions = {
    schema: envSchema,
    dotenv: true,
};


