<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales CRM Lite') ?></title>
</head>
<body>
    <?php
        $currentUser = \App\Core\Auth::user();
        $flashSuccess = \App\Core\Session::getFlash('success');
        $flashError = \App\Core\Session::getFlash('error');
    ?>

    <nav>
        <a href="/">Dashboard</a>

        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <a href="/users">Users</a>
        <?php endif; ?>

        <form method="POST" action="/logout" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
        </form>
    </nav>

    <main>
        <?php if (! empty($flashSuccess)): ?>
            <div role="status">
                <?= e($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($flashError)): ?>
            <div role="alert">
                <?= e($flashError) ?>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>
</body>
</html>
