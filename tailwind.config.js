/* @type {import('tailwindcss').Config} */
const defaultTheme = require("tailwindcss/defaultTheme");

const getColors = () => {
  const colors = [
    "lightblue",
    "lilla",
    "orange",
    "primary",
    "secondary",
    "black",
    "gray",
    "blue",
    "purple",
    "aqua",
    "green",
    "red",
    "yellow",
  ];
  const alertColors = ["success", "warning", "danger"];

  const colorsObject = {};

  for (let i = 0; i < colors.length; i++) {
    const color = colors[i];
    colorsObject[`bb-${color}`] = {
      DEFAULT: `var(--color-${color}-default)`,
      lighter: `var(--color-${color}-lighter)`,
      100: `var(--color-${color}-100)`,
      200: `var(--color-${color}-200)`,
      300: `var(--color-${color}-300)`,
      400: `var(--color-${color}-400)`,
      500: `var(--color-${color}-500)`,
      600: `var(--color-${color}-600)`,
      700: `var(--color-${color}-700)`,
      800: `var(--color-${color}-800)`,
      900: `var(--color-${color}-900)`,
    };
  }

  for (let i = 0; i < alertColors.length; i++) {
    const color = alertColors[i];
    colorsObject[`bb-${color}`] = {
      DEFAULT: `var(--color-${color}-default)`,
      100: `var(--color-${color}-100)`,
      200: `var(--color-${color}-200)`,
      300: `var(--color-${color}-300)`,
      400: `var(--color-${color}-400)`,
      500: `var(--color-${color}-500)`,
    };
  }

  return colorsObject;
};

module.exports = {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./vendor/laravel/jetstream/**/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.vue",
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ["Rubik", "sans-serif"],
      },
      colors: {
        ...getColors(),
      },
      boxShadow: {
        "md-up": "-4px -4px 6px -1px rgb(0 0 0 / 0.1), 0 -2px 4px -2px rgb(0 0 0 / 0.1)",
      },
    },
  },

  plugins: [require("@tailwindcss/forms"), require("@tailwindcss/typography")],
};
