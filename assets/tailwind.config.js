module.exports = {
    // purge: [
    //     '../templates/**/*.php',
    //     // './src/**/*.vue',
    //     // './src/**/*.jsx',
    //     './src/**/*.html',
    // ],
    purge: {
        enabled: false, //pereere.com Do it this way and set it to true because it seems NODE_ENV is not set in production mode
        content: [
            './src/**/*.html',
            './src/**/*.js',
            '../templates/**/*.php',
        ]
    },
    darkMode: false, // or 'media' or 'class'
    theme: {
        extend: {},
    },
    variants: {
        extend: {},
    },
    plugins: [],
}
