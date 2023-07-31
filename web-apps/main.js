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
        showProducts().then(() => {
            productQty(cart);
        })
        loaderImg.classList.remove('d-inline-block');
    }, 1000);
});

function getCart(setCart = false) {
    if (setCart) {
        localStorage.setItem('cart', JSON.stringify(setCart));
    }
    return localStorage.getItem('cart') ? JSON.parse(localStorage.getItem('cart')) : {};
}

function add2Cart(product) {
    let id = product.id;
    if (id in cart) {
        cart[id]['qty'] += 1;
    } else {
        cart[id] = product;
        cart[id]['qty'] = 1;
    }
    getCart(cart);
    getCartSum(cart);
    productQty(cart);
}

function getCartSum(items) {
    let cartSum = Object.entries(items).reduce(function (total, values) {
        const [key, value] = values;
        console.log(value);
        return isNaN(total + (value['qty'] * value['price'])) ? 0 : total + (value['qty'] * value['price']);
    }, 0);
    document.querySelector('.cart-sum').innerText = cartSum + '$';
    return cartSum;
}
function productQty(items) {
    document.querySelectorAll('.product-cart-qty').forEach(item => {
        let id = item.dataset.id;
        if (id in items) {
            item.innerText = items[id]['qty'];
        } else {
            item.innerText = '';

        }
    })

}
let cart = getCart();
getCartSum(cart);
productQty(cart);
productsContainer.addEventListener('click', (e) => {
    if (e.target.classList.contains('add2cart')) {
        e.preventDefault();
        e.target.classList.add('animate__rubberBand');
        // console.log(JSON.parse(e.target.dataset.product));
        add2Cart(JSON.parse(e.target.dataset.product));
        setTimeout(() => {
            e.target.classList.remove('animate__rubberBand');
        }, 1000);
    }
});
