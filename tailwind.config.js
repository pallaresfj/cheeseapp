// tailwind.config.js
module.exports = {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            backgroundImage: {
                'cheese-gradient': 'linear-gradient(to right, #E12D39, #FF606F)',
            },
        },
    },
    plugins: [],
}