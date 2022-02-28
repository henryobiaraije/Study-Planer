module.exports = {
    presets: ['@babel/preset-env','@babel/typescript-preset'],
    env: {
        test: {
            plugins: ["@babel/plugin-transform-runtime"]
        }
    }
};