<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales CRM Lite') ?></title>
</head>
<body>
    <nav>
        <a href="/">Dashboard</a>

        <form method="POST" action="/logout" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
        </form>
    </nav>

    <main>
        <?= $content ?? '' ?>
    </main>
</body>
</html>