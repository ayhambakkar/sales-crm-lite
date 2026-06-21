<section>
    <?php
        $scopeLabel = ($currentUser['role'] ?? '') === 'admin'
            ? 'Global CRM reporting'
            : 'Reports for records assigned to you';

        $pageTitle = 'Reports';
        $pageDescription = $scopeLabel . ' with pipeline, conversion, customer, and follow-up insights.';
        include APP_ROOT . '/src/Views/partials/page-header.php';

        $formatPercent = static fn (float|int $value): string => number_format((float) $value, 1) . '%';
        $formatCurrency = static fn (float|int $value): string => '$' . number_format((float) $value, 2);
        $statusTotal = static fn (array $rows): int => array_sum(array_map(static fn (array $row): int => (int) $row['count'], $rows));

        $kpiCards = [
            ['label' => 'Total Leads', 'value' => number_format((int) $kpis['total_leads']), 'hint' => 'All scoped leads'],
            ['label' => 'Total Customers', 'value' => number_format((int) $kpis['total_customers']), 'hint' => 'All scoped customers'],
            ['label' => 'Total Follow-Ups', 'value' => number_format((int) $kpis['total_follow_ups']), 'hint' => 'Scheduled CRM tasks'],
            ['label' => 'Conversion Rate', 'value' => $formatPercent($conversion_rate), 'hint' => 'Converted leads / total leads'],
            ['label' => 'Open Pipeline', 'value' => $formatCurrency($open_pipeline_value), 'hint' => 'Estimated value of open leads'],
            ['label' => 'Follow-Up Completion', 'value' => $formatPercent($follow_up_completion_rate), 'hint' => 'Done follow-ups / all follow-ups'],
        ];
    ?>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($kpiCards as $kpi): ?>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500"><?= e($kpi['label']) ?></p>
                <p class="mt-3 text-2xl font-semibold text-slate-950"><?= e($kpi['value']) ?></p>
                <p class="mt-1 text-xs text-slate-500"><?= e($kpi['hint']) ?></p>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <?php
            $sections = [
                [
                    'title' => 'Leads by Status',
                    'description' => 'Pipeline distribution for scoped leads.',
                    'rows' => $leads_by_status,
                    'empty' => 'No leads are available for this report scope.',
                ],
                [
                    'title' => 'Customers by Status',
                    'description' => 'Customer health by account status.',
                    'rows' => $customers_by_status,
                    'empty' => 'No customers are available for this report scope.',
                ],
                [
                    'title' => 'Follow-Ups by Status',
                    'description' => 'Task distribution across open, done, and cancelled work.',
                    'rows' => $follow_ups_by_status,
                    'empty' => 'No follow-ups are available for this report scope.',
                ],
            ];
        ?>

        <?php foreach ($sections as $section): ?>
            <?php $total = $statusTotal($section['rows']); ?>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-slate-950"><?= e($section['title']) ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?= e($section['description']) ?></p>

                <?php if ($total === 0): ?>
                    <div class="mt-5 rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        <?= e($section['empty']) ?>
                    </div>
                <?php else: ?>
                    <div class="mt-5 space-y-4">
                        <?php foreach ($section['rows'] as $row): ?>
                            <?php
                                $count = (int) $row['count'];
                                $width = (int) round(($count / max(1, $total)) * 100);
                            ?>
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-700"><?= e(ucwords(str_replace('_', ' ', (string) $row['status']))) ?></span>
                                    <span class="text-slate-500"><?= number_format($count) ?></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-blue-600" style="width: <?= $width ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Leads Created Over Time</h2>
                <p class="mt-1 text-sm text-slate-500">Daily lead creation across the last 30 days.</p>
            </div>

            <?php if (empty($leads_created_over_time)): ?>
                <div class="p-6 text-sm text-slate-500">No leads were created in the last 30 days.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Leads</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($leads_created_over_time as $row): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= e($row['period']) ?></td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-slate-950"><?= number_format((int) $row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Customers Created Over Time</h2>
                <p class="mt-1 text-sm text-slate-500">Daily customer creation across the last 30 days.</p>
            </div>

            <?php if (empty($customers_created_over_time)): ?>
                <div class="p-6 text-sm text-slate-500">No customers were created in the last 30 days.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Customers</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($customers_created_over_time as $row): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= e($row['period']) ?></td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-slate-950"><?= number_format((int) $row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Sales Rep Performance</h2>
                <p class="mt-1 text-sm text-slate-500">Active sales reps with assigned CRM outcomes.</p>
            </div>

            <?php if (empty($sales_rep_performance)): ?>
                <div class="p-6 text-sm text-slate-500">No active sales reps are available for this report.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Sales Rep</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Leads</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Converted</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Customers</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Pipeline</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Follow-Ups Done</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($sales_rep_performance as $row): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <p class="font-medium text-slate-950"><?= e($row['name']) ?></p>
                                        <p class="text-xs text-slate-500"><?= e($row['email']) ?></p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700"><?= number_format((int) $row['total_leads']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700"><?= number_format((int) $row['converted_leads']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700"><?= number_format((int) $row['total_customers']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700"><?= e($formatCurrency((float) $row['open_pipeline_value'])) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700">
                                        <?= number_format((int) $row['completed_follow_ups']) ?> / <?= number_format((int) $row['total_follow_ups']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>
