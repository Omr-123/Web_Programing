// Initialize empty courses array
let courses = [];

// Load courses from localStorage if available
if (localStorage.getItem('courses')) {
    courses = JSON.parse(localStorage.getItem('courses'));
}

// Function to generate courses dynamically
function generateCourses() {
    const courseList = document.getElementById("course-list");
    courseList.innerHTML = ""; // Clear the list before appending

    courses.forEach(course => {
        const box = document.createElement("div");
        box.classList.add("box");

        box.innerHTML = `
            <div class="tutor">
                <img src="assets/admin-profile.jpg" alt="Admin">
                <div class="info">
                    <h3>Course Admin</h3>
                    <span>${new Date(course.date).toLocaleDateString()}</span>
                </div>
            </div>
            <div class="thumb">
                <img src="${course.img}" alt="${course.name}">
                <span>${course.videoCount} videos</span>
            </div>
            <h3 class="title">${course.name}</h3>
            <p>${course.description}</p>
            <a href="#" class="inline-btn">Edit Course</a>
            <button class="delete-btn" onclick="deleteCourse(${course.id})">Delete</button>
        `;

        courseList.appendChild(box);
    });
}

// Add a new course
document.getElementById('add-course-btn').addEventListener('click', () => {
    document.getElementById('course-form').style.display = 'block';  // Show form
});

// Cancel adding a new course
document.getElementById('cancel-btn').addEventListener('click', () => {
    document.getElementById('course-form').style.display = 'none'; // Hide form
});

// Handle the form submission
document.getElementById('new-course-form').addEventListener('submit', (e) => {
    e.preventDefault(); // Prevent form from submitting normally

    const newCourse = {
        id: Date.now(), // Unique ID based on timestamp
        name: document.getElementById('course-name').value,
        img: document.getElementById('course-image').value,
        videoCount: document.getElementById('course-videos').value,
        description: document.getElementById('course-description').value,
        date: new Date(),
    };

    courses.push(newCourse);
    saveCoursesToLocalStorage();
    generateCourses();
    document.getElementById('course-form').style.display = 'none';  // Hide form after submission
    document.getElementById('new-course-form').reset(); // Clear the form
});

// Save courses to localStorage
function saveCoursesToLocalStorage() {
    localStorage.setItem('courses', JSON.stringify(courses));
}

// Delete a course
function deleteCourse(courseId) {
    courses = courses.filter(course => course.id !== courseId);
    saveCoursesToLocalStorage();
    generateCourses();
}

// Initialize on page load
window.onload = generateCourses;

// Function to generate courses dynamically
function generateCourses() {
    const courseList = document.getElementById("course-list");
    courseList.innerHTML = ""; // Clear the list before appending

    courses.forEach(course => {
        const box = document.createElement("div");
        box.classList.add("box");

        box.innerHTML = `
            <div class="tutor">
                <img src="assets/admin-profile.jpg" alt="Admin">
                <div class="info">
                    <h3>Course Admin</h3>
                    <span>${new Date(course.date).toLocaleDateString()}</span>
                </div>
            </div>
            <div class="thumb">
                <img src="${course.img}" alt="${course.name}">
                <span>${course.videoCount} videos</span>
            </div>
            <h3 class="title">${course.name}</h3>
            <p>${course.description}</p>
            <a href="Edit/Edit.html?courseId=${course.id}" class="inline-btn">Edit Course</a>
            <button class="delete-btn" onclick="deleteCourse(${course.id})">Delete</button>
        `;

        courseList.appendChild(box);
    });
}

// Delete a course
function deleteCourse(courseId) {
    courses = courses.filter(course => course.id !== courseId);
    saveCoursesToLocalStorage();
    generateCourses();
}

// Save courses to localStorage
function saveCoursesToLocalStorage() {
    localStorage.setItem('courses', JSON.stringify(courses));
}

// Initialize on page load
window.onload = generateCourses;
