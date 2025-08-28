<html>
    <head>
        <title>Home</title>
    </head>
    <body>

        <?php if (!empty($_SESSION["user"])): ?>
            <p>You are logged in.</p>

            <form action="/logout" method="post">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <p>You are not logged in.</p>
            <p>Please <a href="/login">login</a></p>
        <?php endif; ?>

        <h1>Welcome to the Home Page foo <?= $foo ?></h1>
    </body>
</html>
