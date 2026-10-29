const root = document.documentElement;
const themeToggle = document.querySelector(".theme-toggle");
const lightTheme = {
  "--page": "#f8f7f3",
  "--surface": "#fffefa",
  "--surface-soft": "#efeee8",
  "--ink": "#20251f",
  "--muted": "#6d716a",
  "--line": "#dedfd7",
  "--deep": "#25342b",
  "--deep-text": "#f8f7f3"
};
const darkTheme = {
  "--page": "#171d19",
  "--surface": "#202823",
  "--surface-soft": "#252e28",
  "--ink": "#f0eee6",
  "--muted": "#b2b7ae",
  "--line": "#3b463e",
  "--deep": "#101511",
  "--deep-text": "#f0eee6"
};

if (themeToggle) {
  themeToggle.addEventListener("click", () => {
    const isDark = themeToggle.getAttribute("aria-pressed") !== "true";
    const theme = isDark ? darkTheme : lightTheme;
    Object.entries(theme).forEach(([name, value]) => root.style.setProperty(name, value));
    root.style.colorScheme = isDark ? "dark" : "light";
    themeToggle.setAttribute("aria-pressed", String(isDark));
    themeToggle.setAttribute("aria-label", `Switch to ${isDark ? "light" : "dark"} mode`);
    const label = themeToggle.querySelector(".theme-label");
    if (label) label.textContent = `${isDark ? "Light" : "Dark"} mode`;
  });
}

const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");
if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    const isOpen = menuToggle.getAttribute("aria-expanded") !== "true";
    menuToggle.setAttribute("aria-expanded", String(isOpen));
    menuToggle.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");
    navLinks.classList.toggle("open", isOpen);
  });
  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menuToggle.setAttribute("aria-expanded", "false");
      menuToggle.setAttribute("aria-label", "Open navigation menu");
      navLinks.classList.remove("open");
    });
  });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener("click", (event) => {
    const target = document.querySelector(link.getAttribute("href"));
    if (!target) return;
    event.preventDefault();
    target.scrollIntoView({ behavior: "smooth", block: "start" });
  });
});

const topButton = document.querySelector(".back-to-top");
if (topButton) {
  const updateTopButton = () => topButton.classList.toggle("visible", window.scrollY > 420);
  window.addEventListener("scroll", updateTopButton, { passive: true });
  updateTopButton();
  topButton.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
}

const statNumbers = document.querySelectorAll("[data-count]");
if (statNumbers.length && "IntersectionObserver" in window) {
  const statObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const element = entry.target;
      const target = Number(element.dataset.count);
      const start = performance.now();
      const duration = 1100;
      const increment = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        element.textContent = String(Math.floor(target * progress));
        if (progress < 1) window.requestAnimationFrame(increment);
      };
      window.requestAnimationFrame(increment);
      observer.unobserve(element);
    });
  }, { threshold: 0.35 });
  statNumbers.forEach((number) => statObserver.observe(number));
} else {
  statNumbers.forEach((number) => { number.textContent = number.dataset.count; });
}

const testimonials = [
  {
    quote: "Alex has that rare mix of taste and thoughtfulness. The new site feels like us, only more confident. We couldn’t be happier.",
    name: "Jamie Miller",
    role: "Founder, Goodwell Studio",
    initials: "JM",
    avatarClass: ""
  },
  {
    quote: "From the first conversation, Alex understood what we were trying to do. The whole process felt clear, collaborative, and genuinely fun.",
    name: "Riley Kim",
    role: "Co-founder, Field Notes",
    initials: "RK",
    avatarClass: "avatar-peach"
  },
  {
    quote: "We finally have a digital home that reflects the care we put into our work. Alex made the complicated parts feel easy.",
    name: "Taylor Smith",
    role: "Director, Common Ground",
    initials: "TS",
    avatarClass: "avatar-blue"
  }
];
const testimonial = document.querySelector(".testimonial");
if (testimonial) {
  let slideIndex = 0;
  const quote = testimonial.querySelector("[data-quote]");
  const person = testimonial.querySelector("[data-person]");
  const role = testimonial.querySelector("[data-role]");
  const avatar = testimonial.querySelector("[data-avatar]");
  const showTestimonial = (index) => {
    const item = testimonials[index];
    quote.textContent = item.quote;
    person.textContent = item.name;
    role.textContent = item.role;
    avatar.textContent = item.initials;
    avatar.className = `avatar ${item.avatarClass}`.trim();
  };
  showTestimonial(slideIndex);
  document.querySelectorAll("[data-slide]").forEach((button) => {
    button.addEventListener("click", () => {
      testimonial.classList.add("fading");
      window.setTimeout(() => {
        slideIndex = (slideIndex + (button.dataset.slide === "next" ? 1 : -1) + testimonials.length) % testimonials.length;
        showTestimonial(slideIndex);
        testimonial.classList.remove("fading");
      }, 180);
    });
  });
}

document.querySelectorAll(".faq-question").forEach((button) => {
  button.addEventListener("click", () => {
    const expanded = button.getAttribute("aria-expanded") === "true";
    const answer = button.closest(".faq-item").querySelector(".faq-answer");
    button.setAttribute("aria-expanded", String(!expanded));
    answer.hidden = expanded;
  });
});

const modal = document.querySelector("[data-modal]");
if (modal) {
  const openButton = document.querySelector("[data-modal-open]");
  const closeButtons = modal.querySelectorAll("[data-modal-close]");
  const closeModal = () => {
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    if (openButton) openButton.focus();
  };
  if (openButton) {
    openButton.addEventListener("click", () => {
      modal.classList.add("open");
      modal.setAttribute("aria-hidden", "false");
      modal.querySelector(".modal-close").focus();
    });
  }
  closeButtons.forEach((button) => button.addEventListener("click", closeModal));
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("open")) closeModal();
  });
}

const contactForm = document.querySelector("#contact-form");
if (contactForm) {
  const fields = ["name", "email", "message"].map((name) => contactForm.elements.namedItem(name));
  const status = contactForm.querySelector(".form-status");
  contactForm.addEventListener("submit", (event) => {
    event.preventDefault();
    let firstInvalid = null;
    fields.forEach((field) => {
      const value = field.value.trim();
      let error = "";
      if (!value) error = "Please fill in this field.";
      else if (field.name === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) error = "Enter a valid email address.";
      const errorElement = contactForm.querySelector(`[data-error-for="${field.name}"]`);
      errorElement.textContent = error;
      field.setAttribute("aria-invalid", String(Boolean(error)));
      if (error && !firstInvalid) firstInvalid = field;
    });
    if (firstInvalid) {
      status.textContent = "Please check the highlighted fields.";
      firstInvalid.focus();
      return;
    }
    status.textContent = "Your details look good. This demo doesn’t send messages yet—please email hello@alexmorgan.design.";
  });
  fields.forEach((field) => {
    field.addEventListener("input", () => {
      field.removeAttribute("aria-invalid");
      contactForm.querySelector(`[data-error-for="${field.name}"]`).textContent = "";
      status.textContent = "";
    });
  });
}

const searchInput = document.querySelector("#project-search");
if (searchInput) {
  const cards = [...document.querySelectorAll(".project-card")];
  const count = document.querySelector("[data-project-count]");
  const noResults = document.querySelector("[data-no-results]");
  searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim().toLowerCase();
    let visibleCount = 0;
    cards.forEach((card) => {
      const matches = card.dataset.search.toLowerCase().includes(query);
      card.hidden = !matches;
      if (matches) visibleCount += 1;
    });
    count.textContent = String(visibleCount);
    noResults.hidden = visibleCount > 0;
  });
}

document.querySelectorAll("[data-year]").forEach((element) => {
  element.textContent = String(new Date().getFullYear());
});
