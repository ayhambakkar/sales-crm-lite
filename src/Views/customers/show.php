<section>
    <?php
        ob_start();
    ?>
        <a href="/customers" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Customers</a>
        <a href="/customers/<?= (int) $customer['id'] ?>/edit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Edit</a>
    <?php
        $pageActions = ob_get_clean();
        $pageTitle = $customer['first_name'] . ' ' . $customer['last_name'];
        $pageDescription = 'Customer account details and ownership.';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Company</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['company'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1">
                        <?php
                            $badgeValue = $customer['customer_status'];
                            include APP_ROOT . '/src/Views/partials/status-badge.php';
                        ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['email'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['phone'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['address'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">City</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['city'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Postal Code</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['postal_code'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Country</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['country'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['assigned_to'] ?? 'Unassigned') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['created_at']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Updated</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($customer['updated_at']) ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</dt>
                    <dd class="mt-1 text-sm leading-6 text-slate-700"><?= nl2br(e($customer['notes'] ?? '')) ?></dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="/customers/<?= (int) $customer['id'] ?>/delete" class="rounded-xl border border-red-200 bg-white p-5">
            <?= csrf_field() ?>
            <h2 class="text-base font-semibold text-slate-950">Delete Customer</h2>
            <p class="mt-1 text-sm text-slate-500">Remove this customer account.</p>
            <button type="submit" class="mt-4 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete Customer</button>
        </form>
    </div>
</section>
