// eslint.config.js
import stylisticJs from '@stylistic/eslint-plugin-js';

export default [
    {
        plugins: {
            '@stylistic/js': stylisticJs
        },
        rules: {
            '@stylistic/js/indent': ['error', 4],
            '@stylistic/js/semi': ['error'],
            '@stylistic/js/quotes': ['error', 'single'],
        }
    }
];
