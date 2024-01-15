/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                sp: {
                    DEFAULT: 'var(--sp-color-default)',
                    '50': 'var(--sp-color-50)',
                    '100': 'var(--sp-color-100)',
                    '200': 'var(--sp-color-200)',
                    '300': 'var(--sp-color-300)',
                    '400': 'var(--sp-color-400)',
                    '500': 'var(--sp-color-500)',
                    '600': 'var(--sp-color-600)',
                    '700': 'var(--sp-color-700)',
                    '800': 'var(--sp-color-800)',
                    '900': 'var(--sp-color-900)',
                    'wp': {
                        'bg': 'var(--sp-color-wp-bg)',
                    }
                    // DEFAULT: '#E97225',
                    // '50': '#FADECC',
                    // '100': '#F8D2B9',
                    // '200': '#F4BA94',
                    // '300': '#F0A26F',
                    // '400': '#ED8A4A',
                    // '500': '#E97225',
                    // '600': '#E36717',
                    // '700': '#D05F15',
                    // '800': '#BE5613',
                    // '900': '#AB4E11',
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

