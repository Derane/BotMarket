<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="telegram-web-app.js"></script>
    <style>
        body {
            background-color: var(--tg-theme-bg-color);
            color: var(--tg-theme-text-color);
        }
        button {
            background-color: var(--tg-theme-button-color);
            color: var(--tg-theme-button-text-color);
            border: 0;
            padding: 5px 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <button id="toggle-main-btn">Main Button</button>
    <button id="close-app">Close</button>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, commodi, consequatur culpa cum deleniti deserunt
        explicabo fuga fugiat iste laborum magnam minus molestias nobis perspiciatis quisquam quo saepe tempora
        totam?</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const tg = window.Telegram.WebApp;
    console.log(tg);
    tg.ready();
    tg.expand();
    document.getElementById('close-app').addEventListener('click', () => {
        tg.close();
    })
</script>
</body>
</html>