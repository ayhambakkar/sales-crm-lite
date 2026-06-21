<section>
    <?php
        $buildUrl = static function (array $overrides = []) use ($filters, $sort, $pagination, $currentUser): string {
            $query = [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
                'sort' => $sort['sort'] ?? 'created_at',
                'direction' => $sort['direction'] ?? 'desc',
                'page' => $pagination['page'] ?? 1,
            ];

            if (($currentUser['role'] ?? '') === 'admin') {
                $query['assigned_user_id'] = $filters['assigned_user_id'] ?? '';
            }

            $query = array_merge($query, $overrides);

            foreach ($query as $key => $value) {
                if ($value === '' || $value === null || ($key === 'page' && (int) $value <= 1)) {
                    unset($query[$key]);
                }
            }

            return '/leads' . ($query === [] ? '' : '?' . http_build_query($query));
        };

        $pageTitle = 'Leads';
        $pageDescription = 'Track prospects and early sales opportunities.';
        $pageActions = '<a href="/exports/leads.csv" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Export CSV</a>'
            . '<a href="/leads/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create Lead</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <form method="GET" action="/leads" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-700">Search</label>
            <input
                id="q"
                name="q"
                value="<?= e($filters['q'] ?? '') ?>"
                placeholder="Name, company, email, or phone"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                        <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="priority" class="mb-1.5 block text-sm font-medium text-slate-700">Priority</label>
                <select id="priority" name="priority" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">All priorities</option>
                <?php foreach ($priorities as $priority): ?>
                    <option value="<?= e($priority) ?>" <?= ($filters['priority'] ?? '') === $priority ? 'selected' : '' ?>>
                        <?= e(ucfirst($priority)) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>

        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <div>
                    <label for="assigned_user_id" class="mb-1.5 block text-sm font-medium text-slate-700">Assigned User</label>
                    <select id="assigned_user_id" name="assigned_user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All users</option>
                    <?php foreach ($assignees as $assignee): ?>
                        <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($filters['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
                            <?= e($assignee['first_name'] . ' ' . $assignee['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
            </div>
        <?php endif; ?>

        <div>
                <label for="sort" class="mb-1.5 block text-sm font-medium text-slate-700">Sort By</label>
                <select id="sort" name="sort" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="created_at" <?= ($sort['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>Created date</option>
                <option value="status" <?= ($sort['sort'] ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                <option value="priority" <?= ($sort['sort'] ?? '') === 'priority' ? 'selected' : '' ?>>Priority</option>
                <option value="estimated_value" <?= ($sort['sort'] ?? '') === 'estimated_value' ? 'selected' : '' ?>>Estimated value</option>
                </select>
        </div>

        <div>
                <label for="direction" class="mb-1.5 block text-sm font-medium text-slate-700">Direction</label>
                <select id="direction" name="direction" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="desc" <?= ($sort['direction'] ?? '') === 'desc' ? 'selected' : '' ?>>Descending</option>
                <option value="asc" <?= ($sort['direction'] ?? '') === 'asc' ? 'selected' : '' ?>>Ascending</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Apply</button>
            <?php if (! empty($hasActiveFilters)): ?>
                <a href="/leads" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
            <?php endif; ?>
        </div>
    </form>

    <p class="mb-3 text-sm text-slate-500">
        Showing page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>,
        <?= (int) $pagination['total'] ?> total lead<?= (int) $pagination['total'] === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($leads)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-base font-semibold text-slate-950"><?= ! empty($hasActiveFilters) ? 'No leads match these filters' : 'No leads exist yet' ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?= ! empty($hasActiveFilters) ? 'Adjust the search or clear filters to broaden the result set.' : 'Create the first lead to start tracking your pipeline.' ?></p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Company</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Estimated Value</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium">
                                    <a href="/leads/<?= (int) $lead['id'] ?>" class="text-blue-700 hover:text-blue-900">
                                        <?= e($lead['first_name'] . ' ' . $lead['last_name']) ?>
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($lead['company'] ?? '') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = $lead['status'];
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                    <?php if (! empty($lead['converted_customer_id'])): ?>
                                        <a href="/customers/<?= (int) $lead['converted_customer_id'] ?>" class="mt-1 block text-xs font-medium text-blue-700 hover:text-blue-900">View customer</a>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $priorityValue = $lead['priority'];
                                        include APP_ROOT . '/src/Views/partials/priority-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= $lead['estimated_value'] !== null ? e(number_format((float) $lead['estimated_value'], 2)) : '' ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($lead['assigned_to'] ?? 'Unassigned') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($lead['created_at']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <a href="/leads/<?= (int) $lead['id'] ?>/edit" class="rounded-md border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                                        <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/delete">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
                $paginationLabel = 'Lead pages';
                include APP_ROOT . '/src/Views/partials/pagination.php';
            ?>
        </div>
    <?php endif; ?>
</section>
