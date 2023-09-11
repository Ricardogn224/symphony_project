/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig", "./src/**/*.php"],
  theme: {
    extend: {
      colors: {
        'primary': '#335F8A',
        'secondary': '#F6B12D',
        'accent': '#55D5E0',
        'neutral': '#2F4558',
        'info': '#F26619',
        'purple-logo': '#A1006B'
      },
    },
    screens: {
      'xs': '240px',
      'sm': '640px',
      // => @media (min-width: 640px) { ... }

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }
    },
    minHeight: {
      '1/2': '50%',
    }
  },
  plugins: [
    require('@tailwindcss/aspect-ratio'),
  ],
};
