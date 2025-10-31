// Function to filter courses based on the input in the search bar

function searchCourses() {
    let input = document.getElementById("search").value.toLowerCase(); // Get search input
    let courses = document.querySelectorAll(".course-item"); // Get all course items

    courses.forEach(course => {
        let courseName = course.textContent.toLowerCase(); // Get the course name

        if (courseName.includes(input)) {
            course.style.display = "block"; // Show the course if it matches
        } else {
            course.style.display = "none"; // Hide the course if it doesn't match
        }
    });
}
// Array of courses
const courses = [
    { name: "Web Development", img: "assets/web-programing.jpeg" },
    { name: "Data Science", img: "assets/Data-Science.jpeg" },
    { name: "Machine Learning", img: "assets/Machine-Learning.jpeg" },
    { name: "Graphic Design", img: "assets/Graphic-Designer.jpeg" },
    "Machine Learning",
    "Graphic Design",
    "Digital Marketing",
    "Programming Basics",
    "Cybersecurity Fundamentals",
    "Cloud Computing",
    "Mobile App Development",
    "UI/UX Design",
    "Project Management",
    "DevOps Essentials",
    "Artificial Intelligence",
    "Blockchain Technology",
    "Internet of Things (IoT)"
];

function generateCourses() {
    const courseList = document.getElementById("course-list"); // Get the UL element
    courseList.innerHTML = ""; // Clear any existing courses (in case the page is reloaded)

    // Loop through the courses array and create a list item for each course
    courses.forEach(course => {
        const li = document.createElement("li"); // Create a new <li> element
        li.classList.add("course-item"); // Add the class to the list item

        // Create the HTML structure for the course
        li.innerHTML = `
      <img src="${course.img}" alt="${course.name} Image">
      <h3>${course.name}</h3>
      <p>Learn the essentials of ${course.name} to advance your skills.</p>
    `;

        courseList.appendChild(li); // Append the <li> to the UL
    });
}

// Call the function to generate courses when the page loads
window.onload = generateCourses;

// Search functionality (same as before)
function searchCourses() {
    let input = document.getElementById("search").value.toLowerCase(); // Get search input
    let courses = document.querySelectorAll(".course-item"); // Get all course items

    courses.forEach(course => {
        let courseName = course.textContent.toLowerCase(); // Get the course name

        if (courseName.includes(input)) {
            course.style.display = "block"; // Show the course if it matches
        } else {
            course.style.display = "none"; // Hide the course if it doesn't match
        }
    });
}

