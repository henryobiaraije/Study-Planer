module.exports = {
    presets: [
        '@babel/preset-env',
        '@babel/typescript-preset',
        {targets: {node: 'current'}},
        '@babel/preset-typescript',
    ],
    env: {
        test: {
            plugins: ["@babel/plugin-transform-runtime"]
        }
    }
};