let client_id = null;
let products = [];
let remainingSeconds = 0;
const reservationTime = 600; // Switch to "unlimited" to remove the time limit

fetch('clients/index.php')
  .then(res => res.json())
  .then(data => {
    client_id = data.client_id;
    const stored = localStorage.getItem(`cart_${client_id}`);
    const time = localStorage.getItem(`time_${client_id}`);
    products = stored ? JSON.parse(stored) : [];
    remainingSeconds = time ? parseInt(time) : (reservationTime === "unlimited" ? "unlimited" : reservationTime);

    fetch('products/dinamic.php')
      .then(res => res.json())
      .then(data => {
        data.forEach(p => {
          const inCart = products.find(c => c.id === p.id);
          p.quantity = inCart ? inCart.quantity : 0;
        });
        products = data;
        renderProducts();
        if (reservationTime !== "unlimited") startCountdown();
        else document.getElementById("countdown").textContent = "No time limit for this session.";
      });
  });

function renderProducts() {
  const container = document.getElementById('item');
  container.innerHTML = '';

  products.forEach((p, i) => {
    if (!p.title) return;

    const div = document.createElement('div');
    div.className = 'item';
    div.innerHTML = `
      <strong>${p.title}</strong><br>
      Quantity: <span id="qty-${p.id}">${p.quantity}</span><br>
    `;

    if (p.available === 0) {
      div.innerHTML += `<button onclick="addToWishlist(${p.id})">Notify me when back in stock</button>`;
    } else {
      div.innerHTML += `
        <button onclick="add(${i})">+</button>
        <button onclick="remove(${i})">-</button>
        <div style="font-size: 12px; color: #999;">Available: ${p.available}</div>
      `;
    }

    container.appendChild(div);
  });
}

function saveState() {
  if (reservationTime !== "unlimited") {
    localStorage.setItem(`time_${client_id}`, remainingSeconds);
  }
  localStorage.setItem(`cart_${client_id}`, JSON.stringify(products));
}

function add(i) {
  if (products[i].available === "unlimited" || products[i].quantity < products[i].available) {
    products[i].quantity++;
    document.getElementById(`qty-${products[i].id}`).textContent = products[i].quantity;
    saveState();
  } else {
    alert('No more stock available');
  }
}

function remove(i) {
  if (products[i].quantity > 0) {
    products[i].quantity--;
    document.getElementById(`qty-${products[i].id}`).textContent = products[i].quantity;
    saveState();
  }
}

function pay() {
  const purchasedProducts = products.filter(p => p.quantity > 0);
  if (purchasedProducts.length === 0) return alert('Your cart is empty');

  const data = {
    client: client_id,
    products: purchasedProducts,
    paymentCode: 'payment_' + client_id
  };

  fetch('cart/payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.text())
  .then(msg => {
    alert('Payment confirmed.');
    localStorage.removeItem(`cart_${client_id}`);
    localStorage.removeItem(`time_${client_id}`);
    window.location.href = 'thanks';
  });
}

function cancel() {
  localStorage.removeItem(`cart_${client_id}`);
  localStorage.removeItem(`time_${client_id}`);
  alert('Purchase has been canceled');
  window.location.href = 'cart';
}

function addToWishlist(id) {
  fetch('cart/wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      client: client_id,
      products: [{ id: id }]
    })
  })
  .then(res => res.text())
  .then(data => {
    alert('We’ll notify you when this product is available again.');
    console.log(data);
  });
}

function startCountdown() {
  const countdownElement = document.getElementById("countdown");
  const interval = setInterval(() => {
    if (remainingSeconds <= 0) {
      clearInterval(interval);
      if (confirm("Time to complete your purchase has expired. Do you need more time?")) {
        remainingSeconds = reservationTime;
        saveState();
        startCountdown();
      } else {
        cancel();
      }
    } else {
      const minutes = Math.floor(remainingSeconds / 60);
      const seconds = remainingSeconds % 60;
      countdownElement.textContent = `Time left: ${minutes}:${seconds.toString().padStart(2, '0')}`;
      remainingSeconds--;
      saveState();
    }
  }, 1000);
}
