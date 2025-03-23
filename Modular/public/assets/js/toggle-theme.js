// Description: This script is used to toggle between light, dark, dark-blue, and dark-red themes.

const config = {
  theme: 'system', // Default theme to 'system'
}

// Function to detect the system's theme
const getSystemTheme = () => {
  // Use the prefers-color-scheme media query to get the system's theme
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// Function to apply the selected theme
const update = () => {
  let selectedTheme = config.theme;

  // If the theme is set to 'system', use the system's current theme
  if (selectedTheme === 'system') {
    selectedTheme = getSystemTheme();
  }

  // Apply the selected theme by updating the 'data-theme' attribute
  document.documentElement.dataset.theme = selectedTheme;

  // Ensure the correct classes are applied to the <html> element
  const isLightMode = selectedTheme === 'light';
  const isDarkMode = selectedTheme === 'dark';
  const isDarkModeBlue = selectedTheme === 'dark-mode-blue';
  const isDarkModeRed = selectedTheme === 'dark-mode-red';//add more themes here
  const isDarkModeBrown = selectedTheme === 'dark-mode-brown';//add more themes here
  const isLightModeGreen = selectedTheme === 'light-mode-green';//add more themes here
  const isLightModeBrown = selectedTheme === 'light-mode-brown';//add more themes here
  
  document.documentElement.classList.toggle('light-mode', isLightMode);
  document.documentElement.classList.toggle('dark-mode', isDarkMode);
  document.documentElement.classList.toggle('dark-mode-blue', isDarkModeBlue);
  document.documentElement.classList.toggle('dark-mode-red', isDarkModeRed);//add more themes here
  document.documentElement.classList.toggle('dark-mode-brown', isDarkModeBrown);//add more themes here
  document.documentElement.classList.toggle('light-mode-green', isLightModeGreen);//add more themes here
  document.documentElement.classList.toggle('light-mode-brown', isLightModeBrown);//add more themes here


  // Store the selected theme in localStorage
  localStorage.setItem('theme', selectedTheme);
}

// Retrieve the theme from localStorage, if available
const storedTheme = localStorage.getItem('theme');
if (storedTheme) {
  config.theme = storedTheme; // Set the theme based on stored value
}

// Add event listener to the theme selection dropdown
document.addEventListener('DOMContentLoaded', () => {
  const themeSelection = document.getElementById('theme-selection');
  if (themeSelection) {
    themeSelection.value = config.theme; // Set the dropdown to the current theme

    themeSelection.addEventListener('change', (event) => {
      config.theme = event.target.value;
      update();
    });
  }

  // Initial call to apply the theme when the page loads
  update();
});

// Listen to system theme changes and update if 'system' is selected
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
  if (config.theme === 'system') {
    update(); // Reapply the theme when system theme changes
  }
});
