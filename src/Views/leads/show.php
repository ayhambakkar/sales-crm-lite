<section>
    <?php
        ob_start();
    ?>
        <a href="/leads" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Leads</a>
        <a href="/leads/<?= (int) $lead['id'] ?>/edit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Edit</a>
    <?php
        $pageActions = ob_get_clean();
        $pageTitle = $lead['first_name'] . ' ' . $lead['last_name'];
        $pageDescription = 'Lead details and conversion status.';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Company</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['company'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1">
                        <?php
                            $badgeValue = $lead['status'];
                            include APP_ROOT . '/src/Views/partials/status-badge.php';
                        ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['email'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['phone'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Source</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['source'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</dt>
                    <dd class="mt-1">
                        <?php
                            $priorityValue = $lead['priority'];
                            include APP_ROOT . '/src/Views/partials/priority-badge.php';
                        ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estimated Value</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= $lead['estimated_value'] !== null ? e(number_format((float) $lead['estimated_value'], 2)) : '' ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['assigned_to'] ?? 'Unassigned') ?></dd>
                </div>
                <?php if (! empty($lead['converted_customer_id'])): ?>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Converted Customer</dt>
                        <dd class="mt-1 text-sm">
                            <a href="/customers/<?= (int) $lead['converted_customer_id'] ?>" class="font-medium text-blue-700 hover:text-blue-900">Customer #<?= (int) $lead['converted_customer_id'] ?></a>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if (! empty($lead['converted_at'])): ?>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Converted At</dt>
                        <dd class="mt-1 text-sm text-slate-950"><?= e($lead['converted_at']) ?></dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['created_at']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Updated</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($lead['updated_at']) ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</dt>
                    <dd class="mt-1 text-sm leading-6 text-slate-700"><?= nl2br(e($lead['notes'] ?? '')) ?></dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4">
            <?php if (! empty($canConvert)): ?>
                <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/convert" class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <?= csrf_field() ?>
                    <h2 class="text-base font-semibold text-blue-950">Convert Lead</h2>
                    <p class="mt-1 text-sm text-blue-700">Create a customer from this lead and mark it converted.</p>
                    <button type="submit" class="mt-4 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Convert to Customer</button>
                </form>
            <?php endif; ?>

            <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/delete" class="rounded-xl border border-red-200 bg-white p-5">
                <?= csrf_field() ?>
                <h2 class="text-base font-semibold text-slate-950">Delete Lead</h2>
                <p class="mt-1 text-sm text-slate-500">Remove this lead from the pipeline.</p>
                <button type="submit" class="mt-4 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete Lead</button>
            </form>
        </div>
    </div>

    <?php
        $createFollowUpUrl = '/follow-ups/create?lead_id=' . (int) $lead['id'];
        include APP_ROOT . '/src/Views/partials/follow-up-list.php';
    ?>
</section>
