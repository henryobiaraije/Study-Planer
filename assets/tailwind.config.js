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
        theme: {
            minWidth: {
                '0': '0',
                '1/4': '25%',
                '1/2': '50%',
                '3/4': '75%',
                'full': '100%',
                '300': '300px',
            }
        }
    },
    variants: {
        extend: {
            textColor: [ 'hover'],
        },
    },
    plugins: [],
}
