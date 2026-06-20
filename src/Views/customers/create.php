<section>
    <?php
        $pageTitle = 'Create Customer';
        $pageDescription = 'Create an account record and assign ownership.';
        $pageActions = '<a href="/customers" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Customers</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <form method="POST" action="/customers" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
        <?= csrf_field() ?>
        <?php include APP_ROOT . '/src/Views/customers/form.php'; ?>
        <div class="mt-6 flex justify-end gap-3">
            <a href="/customers" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create Customer</button>
        </div>
    </form>
</section>
