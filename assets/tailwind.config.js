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
        extend: {
            colors: {
                sp: {
                    '': '#9e9e9e',
                    '50': '#fafafa',
                    '100': '#f5f5f5',
                    '200': '#eeeeee',
                    '300': '#e0e0e0',
                    '400': '#bdbdbd',
                    '500': '#9e9e9e',
                    '600': '#757575',
                    '700': '#616161',
                    '800': '#424242',
                    '900': '#212121',
                },
            },
        },
    },
    variants: {
        extend: {
            textColor: ['hover'],
        },
    },
    plugins: [],
}
