<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales CRM Lite') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-slate-950 px-4 py-10">

    <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-md items-center">
        <div class="w-full">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold text-white shadow-lg shadow-blue-950/30">
                    S
                </div>
                <h1 class="text-2xl font-semibold text-white">Sales CRM Lite</h1>
                <p class="mt-2 text-sm text-slate-400">Sign in to manage leads, customers, and team access.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white p-8 shadow-2xl shadow-slate-950/40">
                <?= $content ?>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                &copy; <?= date('Y') ?> Sales CRM Lite
            </p>
        </div>

    </div>

</body>
</html>
