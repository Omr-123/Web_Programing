var courseProgress = document.querySelectorAll('.course-progress');
courseProgress.forEach((e) => {
    e.style.setProperty('--width', e.getAttribute('value'));
})