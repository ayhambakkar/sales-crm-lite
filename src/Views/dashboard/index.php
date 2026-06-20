<?php
    $stats = $stats ?? [];
    $latest_leads = $latest_leads ?? [];
    $latest_customers = $latest_customers ?? [];
    $recent_conversions = $recent_conversions ?? [];
    $scopeLabel = ($currentUser['role'] ?? '') === 'admin'
        ? 'Global CRM overview'
        : 'Your assigned CRM records';

    ob_start();
?>
    <a href="/leads/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">New Lead</a>
    <a href="/customers/create" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">New Customer</a>
    <a href="/leads" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Leads</a>
    <a href="/customers" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Customers</a>
<?php
    $pageActions = ob_get_clean();
    $pageTitle = 'Dashboard';
    $pageDescription = $scopeLabel . ' with current pipeline and customer health.';
    include APP_ROOT . '/src/Views/partials/page-header.php';

    $kpis = [
        ['label' => 'Total Leads', 'value' => number_format((int) $stats['total_leads']), 'hint' => 'All scoped leads'],
        ['label' => 'Open Leads', 'value' => number_format((int) $stats['open_leads']), 'hint' => 'New, contacted, qualified'],
        ['label' => 'New Leads', 'value' => number_format((int) $stats['new_leads']), 'hint' => 'Waiting for first touch'],
        ['label' => 'Qualified Leads', 'value' => number_format((int) $stats['qualified_leads']), 'hint' => 'Ready for next step'],
        ['label' => 'Converted Leads', 'value' => number_format((int) $stats['converted_leads']), 'hint' => 'Won as customers'],
        ['label' => 'Lost Leads', 'value' => number_format((int) $stats['lost_leads']), 'hint' => 'Closed as lost'],
        ['label' => 'Total Customers', 'value' => number_format((int) $stats['total_customers']), 'hint' => 'All scoped customers'],
        ['label' => 'Active Customers', 'value' => number_format((int) $stats['active_customers']), 'hint' => 'Currently active'],
        ['label' => 'VIP Customers', 'value' => number_format((int) $stats['vip_customers']), 'hint' => 'High-value accounts'],
        ['label' => 'Open Pipeline', 'value' => '$' . number_format((float) $stats['estimated_open_pipeline_value'], 2), 'hint' => 'Estimated open value'],
        ['label' => 'Conversion Rate', 'value' => number_format((float) $stats['conversion_rate'], 1) . '%', 'hint' => 'Converted / total leads'],
    ];
?>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($kpis as $kpi): ?>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500"><?= e($kpi['label']) ?></p>
            <p class="mt-3 text-2xl font-semibold text-slate-950"><?= e($kpi['value']) ?></p>
            <p class="mt-1 text-xs text-slate-500"><?= e($kpi['hint']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<section class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-slate-950">Pipeline Overview</h2>
                <p class="mt-1 text-sm text-slate-500">Current lead distribution in your dashboard scope.</p>
            </div>
            <?php
                $badgeValue = ($currentUser['role'] ?? '') === 'admin' ? 'admin' : 'sales_rep';
                $badgeLabel = ($currentUser['role'] ?? '') === 'admin' ? 'Admin Scope' : 'Sales Rep Scope';
                include APP_ROOT . '/src/Views/partials/status-badge.php';
            ?>
        </div>

        <?php
            $totalLeads = max(1, (int) $stats['total_leads']);
            $pipelineRows = [
                ['label' => 'New', 'value' => (int) $stats['new_leads'], 'bar' => 'bg-sky-500'],
                ['label' => 'Qualified', 'value' => (int) $stats['qualified_leads'], 'bar' => 'bg-emerald-500'],
                ['label' => 'Converted', 'value' => (int) $stats['converted_leads'], 'bar' => 'bg-blue-600'],
                ['label' => 'Lost', 'value' => (int) $stats['lost_leads'], 'bar' => 'bg-red-500'],
            ];
        ?>

        <div class="mt-6 space-y-4">
            <?php foreach ($pipelineRows as $row): ?>
                <?php $width = (int) round(($row['value'] / $totalLeads) * 100); ?>
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700"><?= e($row['label']) ?></span>
                        <span class="text-slate-500"><?= number_format($row['value']) ?></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full <?= e($row['bar']) ?>" style="width: <?= $width ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-950">Quick Actions</h2>
        <p class="mt-1 text-sm text-slate-500">Jump into common CRM workflows.</p>
        <div class="mt-5 grid gap-3">
            <a href="/leads/create" class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create Lead</a>
            <a href="/customers/create" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Create Customer</a>
            <a href="/leads?status=qualified" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Review Qualified Leads</a>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-2">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-950">Latest Leads</h2>
            <p class="mt-1 text-sm text-slate-500">The 5 newest leads in scope.</p>
        </div>

        <?php if (empty($latest_leads)): ?>
            <div class="p-6 text-sm text-slate-500">No leads are available yet.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lead</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($latest_leads as $lead): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <a href="/leads/<?= (int) $lead['id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($lead['first_name'] . ' ' . $lead['last_name']) ?></a>
                                    <p class="text-xs text-slate-500"><?= e($lead['company'] ?? 'No company') ?></p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = $lead['status'];
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $priorityValue = $lead['priority'];
                                        include APP_ROOT . '/src/Views/partials/priority-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= $lead['estimated_value'] !== null ? '$' . e(number_format((float) $lead['estimated_value'], 2)) : '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-950">Latest Customers</h2>
            <p class="mt-1 text-sm text-slate-500">The 5 newest customer accounts in scope.</p>
        </div>

        <?php if (empty($latest_customers)): ?>
            <div class="p-6 text-sm text-slate-500">No customers are available yet.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">City</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($latest_customers as $customer): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <a href="/customers/<?= (int) $customer['id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></a>
                                    <p class="text-xs text-slate-500"><?= e($customer['company'] ?? 'No company') ?></p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = $customer['customer_status'];
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($customer['city'] ?? '') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($customer['assigned_to'] ?? 'Unassigned') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="text-base font-semibold text-slate-950">Recently Converted Leads</h2>
        <p class="mt-1 text-sm text-slate-500">Latest lead-to-customer conversions in scope.</p>
    </div>

    <?php if (empty($recent_conversions)): ?>
        <div class="p-6 text-sm text-slate-500">No converted leads are available yet.</div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lead</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Converted At</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recent_conversions as $conversion): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <a href="/leads/<?= (int) $conversion['id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($conversion['first_name'] . ' ' . $conversion['last_name']) ?></a>
                                <p class="text-xs text-slate-500"><?= e($conversion['company'] ?? 'No company') ?></p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <?php if (! empty($conversion['converted_customer_id'])): ?>
                                    <a href="/customers/<?= (int) $conversion['converted_customer_id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($conversion['customer_name'] ?? 'Customer #' . (int) $conversion['converted_customer_id']) ?></a>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($conversion['converted_at'] ?? '') ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($conversion['assigned_to'] ?? 'Unassigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
