import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Segoe UI', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                idej: {
                    50: '#eef5ff',
                    100: '#d9e9ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#16304f',
                    900: '#0f2138',
                    950: '#081426',
                },
            },
            boxShadow: {
                'soft': '0 18px 45px -28px rgba(15, 23, 42, 0.35)',
                'panel': '0 10px 30px -24px rgba(15, 23, 42, 0.32)',
            },
        },
    },

    plugins: [forms],
};
