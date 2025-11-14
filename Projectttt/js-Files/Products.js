const ITEMS_PER_PAGE = 9;

const products = [
    {
        id: 1,
        name: "JavaScript Course ⚡",
        price: "$30",
        image: "assets/js-course.png",
        description: "Learn JavaScript from zero to hero.",
        instructor: "Mohamed Samir",
        Duration: "6 weeks",
        Level: "Beginner to Advanced"
    },
    {
        id: 2,
        name: "Python Course 🐍",
        price: "$25",
        image: "assets/python-course.png",
        description: "Master Python with practical exercises.",
        instructor: "Sara Ahmed",
        Duration: "5 weeks",
        Level: "Beginner to Intermediate"
    },
    {
        id: 3,
        name: "Web Development Bootcamp 🌐",
        price: "$40",
        image: "assets/Web-dev.png",
        description: "HTML, CSS & JavaScript full course.",
        instructor: "Ahmed Ali",
        Duration: "8 weeks",
        Level: "Beginner to Advanced"
    },
    {
        id: 4,
        name: "Data Science Essentials 📊",
        price: "$35",
        image: "assets/Data-Science-course.png",
        description: "Comprehensive data analysis and visualization.",
        instructor: "John Smith",
        Duration: "7 weeks",
        Level: "Intermediate to Advanced"
    },
    {
        id: 5,
        name: "Graphic Design Basics 🎨",
        price: "$29.99",
        image: "assets/Graphic-Desgin-course.png",
        description: "Typography, color theory and Adobe basics.",
        instructor: "Laura Davis",
        Duration: "10 weeks",
        Level: "Beginner to Intermediate"
    },
    {
        id: 6,
        name: "Machine Learning A-Z 🤖",
        price: "$45",
        image: "assets/Machine Leanging-course.png",
        description: "Algorithms, model evaluation and deployment.",
        instructor: "David Wilson",
        Duration: "24 weeks",
        Level: "Intermediate to Advanced"
    },
    {
        id: 7,
        name: "UX Design ✨",
        price: "$34",
        image: "assets/UX-desgin-course.png",
        description: "User research, wireframing and prototyping.",
        instructor: "Salma Nasser",
        Duration: "6 weeks",
        Level: "Beginner to Intermediate"
    },
    {
        id: 8,
        name: "Java Course ☕",
        price: "$28",
        image: "assets/Java-course.png",
        description: "Core Java concepts, OOP and small projects.",
        instructor: "Khaled Mansour",
        Duration: "8 weeks",
        Level: "Beginner to Intermediate"
    },
    {
        id: 9,
        name: "Digital Marketing 📣",
        price: "$26",
        image: "assets/Digital-Marketing-course.png",
        description: "SEO, social media and analytics.",
        instructor: "Mona El-Gendy",
        Duration: "5 weeks",
        Level: "Beginner to Intermediate"
    },
    {
        id: 10,
        name: "Cloud Computing Fundamentals",
        price: "$49,99",
        image: "assets/Cloud-Computing-Course.png",
        description: "AWS, Azure, Google Cloud basics and services overview.",
        instructor: "Said Ahmed",
        Duration: "11 Hours",
        Level: "Beginner to Advanced"
    },
    {
        id: 11,
        name: "Computer Vision Basics",
        price: "$43,99",
        image: "assets/C-vision.png",
        description: "Introduction to computer vision techniques and applications.",
        instructor: "Hossam Eldin",
        Duration: "23 weeks",
        Level: "Beginner to intermediate"
    },
    {
        id: 12,
        name: "Data Entry Course",
        price: "$19,99",
        image: "assets/Data-Entry.png",
        description: "Learn data entry skills and techniques for accuracy and efficiency.",
        instructor: "Michael Brown",
        Duration: "7 weeks",
        Level: "Beginner to Advanced"
    }
];

function displayProducts(productsToDisplay = products) {
    const container = document.getElementById("product-list");
    let html = "";

    productsToDisplay.forEach(product => {
        html += `
    <div class="course" onclick="openCourse(${product.id})">
        <div class="course-image">
          <img src="${product.image}" alt="${product.name}">
        </div>
        <div class="course-details">
            <h2 class="course-title">${product.name}</h2>
            <p class="course-description">${product.description}</p>
            <div class="course-meta">
                <p><strong>Instructor:</strong> ${product.instructor}</p>
                <p><strong>Duration:</strong> ${product.Duration}</p>
                <p><strong>Level:</strong> ${product.Level}</p>
            </div>
            <div class="course-price">
                <span class="price">${product.price}</span>
                <a href="index.html"><button class="enroll-btn">Course Info</button></a>
                <button class="enroll-btn" onclick="addToCart(${product.id}); event.stopPropagation();">Buy Now</button>
            </div>    
        </div>
    </div>
        `;
    });

    container.innerHTML = html;
}

// Search functionality
const searchInput = document.querySelector(".Products-Container");

searchInput.addEventListener("input", (e) => {
    const searchTerm = e.target.value.toLowerCase();

    if (searchTerm === "") {
        displayProducts(products);
        return;
    }

    const filteredProducts = products.filter(product =>
        product.name.toLowerCase().includes(searchTerm) ||
        product.description.toLowerCase().includes(searchTerm) ||
        product.instructor.toLowerCase().includes(searchTerm) ||
        product.Level.toLowerCase().includes(searchTerm)
    );

    displayProducts(filteredProducts);
});

function openCourse(id) {
    localStorage.setItem("selectedCourse", id);
    window.location.href = "course.html";
}

let cart = JSON.parse(localStorage.getItem("cart")) || [];

function addToCart(id) {
    const product = products.find(p => p.id === id);
    if (product) {
        cart.push(product);
        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartDisplay();
        alert(`"${product.name}" added to cart!`);
    }
}




function updateCartDisplay() {
    const cartItemsDisplay = document.getElementById("cart-items-display");
    const cartCount = document.getElementById("cart-count");
    const badge = document.getElementById("badge");
    const cartTotal = document.getElementById("cart-total");

    cartCount.textContent = cart.length;
    badge.textContent = cart.length;

    if (cart.length === 0) {
        cartItemsDisplay.innerHTML = '<p class="empty-message">Your cart is empty</p>';
        cartTotal.textContent = "$0";
        return;
    }

    let html = "";
    let total = 0;

    cart.forEach((item, index) => {
        const price = parseFloat(item.price.replace("$", ""));
        total += price;

        html += `
            <div class="cart-item-mini">
                <div class="cart-item-mini-info">
                    <h4>${item.name}</h4>
                    <p>${item.price}</p>
                </div>
                <button class="remove-item-btn" onclick="removeFromCartSidebar(${index})">Remove</button>
            </div>
        `;
    });

    cartItemsDisplay.innerHTML = html;
    cartTotal.textContent = "$" + total.toFixed(2);
}

function removeFromCartSidebar(index) {
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartDisplay();
}

function toggleCart() {
    document.querySelector(".cart-sidebar").classList.toggle("active");
}

displayProducts();
updateCartDisplay();