let slideIndex = 0;
const slides = document.querySelectorAll('.slide');

function showSlide(index) {
    slides.forEach(s => s.classList.remove('active'));
    if (index >= slides.length) slideIndex = 0;
    if (index < 0) slideIndex = slides.length - 1;
    slides[slideIndex].classList.add('active');
}

function moveSlide(step) {
    slideIndex += step;
    showSlide(slideIndex);
}

if (slides.length > 0) {
    setInterval(() => moveSlide(1), 5000);
}