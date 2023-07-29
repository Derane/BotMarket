const productsContainer = document.getElementById('products-list');
const loaderBtn = document.getElementById('loader-btn');
const loaderImg = document.getElementById('loader-img');
let page = 1;

async function getProducts() {
    const res = await fetch(`page1.php?page=${page}`);
    return res.text();
}

async function showProducts() {
    const products = await getProducts();
    if (products) {
        productsContainer.insertAdjacentHTML('beforeend', products);
    } else {
        loaderBtn.classList.add('d-none');
    }
}

loaderBtn.addEventListener('click', () => {
    loaderImg.classList.add('d-inline-block');
    setTimeout(() => {
        page++;
        showProducts();
        loaderImg.classList.remove('d-inline-block');
    }, 1000);
});

function getCart(setCar = false) {
    if (setCar) {
        localStorage.setItem('cart', JSON.stringify(setCar))

    }
    return localStorage.getItem('cart')
        ? JSON.parse(localStorage.getItem('cart'))
        : {};
}

function addToCart(product) {
    let id = product.id;
    if (id in cart) {
        cart[id]['gty'] += 1;
    } else {
        cart[id] = product;
        cart[id]['gty'] = 1;
    }
    getCart(cart);
}

let cart = getCart();

productsContainer.addEventListener('click', (e) => {
    if (e.target.classList.contains('add2cart')) {
        e.preventDefault();
        e.target.classList.add('animate_rubberBand');
        addToCart(JSON.parse(e.target.dataset.product));
        setTimeout(() => {
            e.target.classList.remove('animate_rubberBand');
        }, 1000)
    }
});