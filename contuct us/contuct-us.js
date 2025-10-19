// Function to handle form submission
document.getElementById('contactForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent the default form submission

    // Get form data
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const message = document.getElementById('message').value;

    // Validate if all fields are filled
    if (name && email && message) {
        // Display success message
        document.getElementById('responseMessage').textContent = "Thank you for your message, " + name + "! We will get back to you soon.";

        // Clear the form fields
        document.getElementById('contactForm').reset();
    } else {
        // If any field is empty, show an error message
        document.getElementById('responseMessage').textContent = "Please fill out all fields.";
        document.getElementById('responseMessage').style.color = '#e74c3c'; // Red for error
    }
});
