// Retrieve courses from localStorage
let courses = JSON.parse(localStorage.getItem('courses')) || [];

document.getElementById('edit-course-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const courseId = new URLSearchParams(window.location.search).get('courseId');
    const courseName = document.getElementById('course-name').value;
    const courseImage = document.getElementById('course-image').value;
    const courseVideos = document.getElementById('course-videos').value;
    const courseDescription = document.getElementById('course-description').value;

    if (courseId) {
        // Editing an existing course
        const courseIndex = courses.findIndex(course => course.id == courseId);
        courses[courseIndex] = {
            ...courses[courseIndex],
            name: courseName,
            img: courseImage,
            videoCount: courseVideos,
            description: courseDescription,
            date: new Date(),
        };
        localStorage.setItem('courses', JSON.stringify(courses));
        window.location.href = '../PDetail.html'; // Redirect back to the main dashboard
    } else {
        // Adding a new course
        const newCourse = {
            id: Date.now(),
            name: courseName,
            img: courseImage,
            videoCount: courseVideos,
            description: courseDescription,
            date: new Date(),
        };
        courses.push(newCourse);
        localStorage.setItem('courses', JSON.stringify(courses));
        window.location.href = '../PDetail.html'; // Redirect back to the main dashboard
    }
});

// Check if we're editing an existing course and populate the form
const courseId = new URLSearchParams(window.location.search).get('courseId');
if (courseId) {
    const course = courses.find(course => course.id == courseId);
    if (course) {
        document.getElementById('course-name').value = course.name;
        document.getElementById('course-image').value = course.img;
        document.getElementById('course-videos').value = course.videoCount;
        document.getElementById('course-description').value = course.description;
        document.getElementById('form-title').innerText = 'Edit Course';
    }
}
