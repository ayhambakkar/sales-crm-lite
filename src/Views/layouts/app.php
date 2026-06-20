<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales CRM Lite') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-slate-50">
    <?php
        $currentUser = \App\Core\Auth::user();
        $flashSuccess = \App\Core\Session::getFlash('success');
        $flashError = \App\Core\Session::getFlash('error');
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $isActive = static function (string $path) use ($currentPath): bool {
            return $path === '/' ? $currentPath === '/' : str_starts_with($currentPath, $path);
        };
        $roleLabel = ($currentUser['role'] ?? '') === 'admin' ? 'Admin' : 'Sales Rep';
    ?>

    <div class="min-h-screen lg:flex">
        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:block">
            <div class="flex h-16 items-center border-b border-slate-200 px-6">
                <a href="/" class="text-lg font-semibold text-slate-950">Sales CRM Lite</a>
            </div>
            <nav class="space-y-1 px-3 py-5">
                <?php
                    $href = '/';
                    $label = 'Dashboard';
                    $active = $isActive('/');
                    include APP_ROOT . '/src/Views/partials/nav-item.php';

                    $href = '/leads';
                    $label = 'Leads';
                    $active = $isActive('/leads');
                    include APP_ROOT . '/src/Views/partials/nav-item.php';

                    $href = '/customers';
                    $label = 'Customers';
                    $active = $isActive('/customers');
                    include APP_ROOT . '/src/Views/partials/nav-item.php';

                    if (($currentUser['role'] ?? '') === 'admin') {
                        $href = '/users';
                        $label = 'Users';
                        $active = $isActive('/users');
                        include APP_ROOT . '/src/Views/partials/nav-item.php';
                    }
                ?>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex min-h-16 flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-slate-950">Sales CRM Lite</p>
                        <p class="text-xs text-slate-500">Pipeline and customer workspace</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-950">
                                <?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?>
                            </p>
                            <p class="text-xs text-slate-500"><?= e($roleLabel) ?></p>
                        </div>

                        <form method="POST" action="/logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                <nav class="flex gap-1 overflow-x-auto border-t border-slate-100 px-4 py-2 lg:hidden">
                    <?php
                        $href = '/';
                        $label = 'Dashboard';
                        $active = $isActive('/');
                        include APP_ROOT . '/src/Views/partials/nav-item.php';

                        $href = '/leads';
                        $label = 'Leads';
                        $active = $isActive('/leads');
                        include APP_ROOT . '/src/Views/partials/nav-item.php';

                        $href = '/customers';
                        $label = 'Customers';
                        $active = $isActive('/customers');
                        include APP_ROOT . '/src/Views/partials/nav-item.php';

                        if (($currentUser['role'] ?? '') === 'admin') {
                            $href = '/users';
                            $label = 'Users';
                            $active = $isActive('/users');
                            include APP_ROOT . '/src/Views/partials/nav-item.php';
                        }
                    ?>
                </nav>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <?php include APP_ROOT . '/src/Views/partials/flash.php'; ?>
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>
</body>
</html>
