<?php
    $pageTitle = 'Dashboard';
    $pageDescription = 'A focused workspace for managing pipeline activity and customer accounts.';
    include APP_ROOT . '/src/Views/partials/page-header.php';
?>

<div class="grid gap-4 md:grid-cols-3">
    <a href="/leads" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-200 hover:shadow-md">
        <p class="text-sm font-medium text-slate-500">Pipeline</p>
        <h2 class="mt-2 text-lg font-semibold text-slate-950">Leads</h2>
        <p class="mt-2 text-sm text-slate-500">Review prospects, qualification status, priority, and conversion readiness.</p>
    </a>

    <a href="/customers" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-200 hover:shadow-md">
        <p class="text-sm font-medium text-slate-500">Accounts</p>
        <h2 class="mt-2 text-lg font-semibold text-slate-950">Customers</h2>
        <p class="mt-2 text-sm text-slate-500">Manage active, inactive, and VIP customer relationships in one place.</p>
    </a>

    <?php if ((\App\Core\Auth::user()['role'] ?? '') === 'admin'): ?>
        <a href="/users" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-200 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">Administration</p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">Users</h2>
            <p class="mt-2 text-sm text-slate-500">Maintain team access, active status, and role assignments.</p>
        </a>
    <?php endif; ?>
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-slate-950">Today’s Focus</h2>
    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
        Keep lead statuses current, convert qualified opportunities when they become customers,
        and make sure ownership is assigned so sales representatives see the right records.
    </p>
</div>
