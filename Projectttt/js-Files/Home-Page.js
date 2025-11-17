
document.addEventListener('DOMContentLoaded', () => {
    const testimonials = document.querySelectorAll('.testimonial');
    const nextBtn = document.getElementById('next');
    const prevBtn = document.getElementById('prev');
    let current = 0;

    function showTestimonial(index) {
        testimonials.forEach((t) => t.classList.remove('active'));
        testimonials[index].classList.add('active');
    }

    showTestimonial(current);

    nextBtn.addEventListener('click', () => {
        current = (current + 1) % testimonials.length;
        showTestimonial(current);
    });

    prevBtn.addEventListener('click', () => {
        current = (current - 1 + testimonials.length) % testimonials.length;
        showTestimonial(current);
    });

    setInterval(() => {
        current = (current + 1) % testimonials.length;
        showTestimonial(current);
    }, 5000);
});


document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.number');

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / 200;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target;
            }
        }

        updateCount();
    });
});




