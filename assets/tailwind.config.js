module.exports = {
  // purge: [
  //     '../templates/**/*.php',
  //     // './src/**/*.vue',
  //     // './src/**/*.jsx',
  //     './src/**/*.html',
  // ],
  important: '.sp',
  mode: 'jit',
  purge: {
    enabled: false, //pereere.com Do it this way and set it to true because it seems NODE_ENV is not set in production mode
    content: [
      './src/**/*.html',
      './src/**/*.js',
      '../templates/**/*.php',
    ],
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        sp: {
          DEFAULT: '#E97225',
          '50': '#FADECC',
          '100': '#F8D2B9',
          '200': '#F4BA94',
          '300': '#F0A26F',
          '400': '#ED8A4A',
          '500': '#E97225',
          '600': '#E36717',
          '700': '#D05F15',
          '800': '#BE5613',
          '900': '#AB4E11',
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
};
