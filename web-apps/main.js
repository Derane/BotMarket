$(document).ready(function () {
    const tg = window.Telegram.WebApp;
    tg.ready();
    tg.expand();

    const productsContainer = $('#products-list');
    const loaderBtn = $('#loader-btn');
    const loaderImg = $('#loader-img');
    const cartTable = $('table');
    let page = 1;
    let cart = getCart();

    async function getProducts() {
        const res = await fetch(`page1.php?page=${page}`);
        return res.text();
    }

    async function showProducts() {
        const products = await getProducts();
        if (products) {
            productsContainer.append(products);
        } else {
            loaderBtn.addClass('d-none');
        }
    }

    loaderBtn.on('click', function () {
        loaderImg.addClass('d-inline-block');
        setTimeout(function () {
            page++;
            showProducts()
                .then(function () {
                    productQty(cart);
                });
            loaderImg.removeClass('d-inline-block');
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
        cartContent(cart);
    }

    function getCartSum(items) {
        let cartSum = Object.entries(items).reduce(function (total, values) {
            const [key, value] = values;
            return total + (value['qty'] * value['price']);
        }, 0);
        $('.cart-sum').text(cartSum / 100 + '$');
        return cartSum;
    }

    function productQty(items) {
        $('.product-cart-qty').each(function () {
            let id = $(this).data('id');
            if (id in items) {
                $(this).text(items[id]['qty']);
            } else {
                $(this).text('');
            }
        });
    }

    function cartContent(items) {
        let cartTableBody = $('.table tbody');
        let cartEmpty = $('.empty-cart');
        let qty = Object.keys(items).length;
        if (qty) {
            tg.MainButton.show();
            tg.MainButton.setParams({
                text: `CHECKOUT: ${getCartSum(items) / 100}$`,
                color: '#d7b300'
            });
            cartTable.removeClass('d-none');
            cartEmpty.removeClass('d-block').addClass('d-none');
            cartTableBody.empty();
            Object.keys(items).forEach(function (key) {
                cartTableBody.append(`
<tr class="align-middle animate__animated">
    <th scope="row">${key}</th>
    <td><img src="img/${items[key]['img']}" class="cart-img" alt=""></td>
    <td>${items[key]['title']}</td>
    <td>${items[key]['qty']}</td>
    <td>${items[key]['price'] / 100}</td>
    <td data-id="${key}"><button class="btn del-item">🗑</button></td>
</tr>
`);
            });
        } else {
            tg.MainButton.hide();
            cartTableBody.empty();
            cartTable.addClass('d-none');
            cartEmpty.removeClass('d-none').addClass('d-block');
        }
    }

    productsContainer.on('click', '.add2cart', function (e) {
        e.preventDefault();
        $(this).addClass('animate__rubberBand');
        const product = $(this).data('product')
        add2Cart(product);
        setTimeout(function () {
            $(this).removeClass('animate__rubberBand');
        }, 1000);
    });

    cartTable.on('click', '.del-item', function (e) {
        const target = $(this).closest('tr');
        if (target) {
            target.addClass('animate__zoomOut');
            setTimeout(function () {
                const id = target.find('[data-id]').data('id');
                delete cart[id];
                getCart(cart);
                getCartSum(cart);
                productQty(cart);
                cartContent(cart);
            }, 300);
        }
    });

    tg.MainButton.onClick(function () {
        $.ajax({
            url: '../index.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json;charset=utf-8',
            data: JSON.stringify({
                query_id: tg.initDataUnsafe.query_id,
                user: tg.initDataUnsafe.user,
                cart: cart,
                total_sum: getCartSum(cart)
            }),
            success: function (data) {
                console.log(data);
                if (data.res) {
                    cart = getCart({});
                    getCartSum(cart);
                    productQty(cart);
                    cartContent(cart);
                    tg.close();
                } else {
                    alert(data.answer);
                }
            }
        });
    });
    showProducts();
    getCartSum(cart);
    productQty(cart);
    cartContent(cart);
});