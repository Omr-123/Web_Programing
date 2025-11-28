
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


$(document).ready(function () {
    $('.number').each(function () {
        const $counter = $(this);
        const target = +$counter.data('target');
        let count = +$counter.text();
        const increment = target / 200;

        const updateCount = () => {
            if (count < target) {
                count = Math.ceil(count + increment);
                $counter.text(count);
                setTimeout(updateCount, 20);
            } else {
                $counter.text(target);
            }
        };

        updateCount();
    });
});


