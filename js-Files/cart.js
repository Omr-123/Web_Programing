let cart = JSON.parse(localStorage.getItem("cart")) || [];

function displayCart() {
    const cartContainer = document.getElementById("cart-items");
    const totalPriceElement = document.getElementById("total-price");

    if (cart.length === 0) {
        cartContainer.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
        totalPriceElement.textContent = "$0";
        return;
    }

    let html = "";
    let total = 0;

    cart.forEach((item, index) => {
        const price = parseFloat(item.price.replace("$", ""));
        total += price;

        html += `
            <div class="cart-item">
                <div class="item-details">
                    <h3>${item.name}</h3>
                    <p><strong>Instructor:</strong> ${item.instructor}</p>
                    <p><strong>Duration:</strong> ${item.Duration}</p>
                    <div class="item-price">${item.price}</div>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${index})">Remove</button>
            </div>
        `;
    });

    cartContainer.innerHTML = html;
    totalPriceElement.textContent = "$" + total.toFixed(2);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    displayCart();
}

displayCart();