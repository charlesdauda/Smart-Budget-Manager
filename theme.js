const themeToggle = document.getElementById("theme-toggle");
const body = document.body;
const icon = themeToggle.querySelector("i");

// Check saved theme or system preference
const savedTheme = localStorage.getItem("theme");
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

// Apply saved or system theme
if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
  body.classList.add("dark-mode");
  icon.classList.replace("ri-sun-line", "ri-moon-line");
} else {
  body.classList.remove("dark-mode");
  icon.classList.replace("ri-moon-line", "ri-sun-line");
}

// Toggle theme manually
themeToggle.addEventListener("click", () => {
  body.classList.toggle("dark-mode");

  if (body.classList.contains("dark-mode")) {
    icon.classList.replace("ri-sun-line", "ri-moon-line");
    localStorage.setItem("theme", "dark");
  } else {
    icon.classList.replace("ri-moon-line", "ri-sun-line");
    localStorage.setItem("theme", "light");
  }
});






