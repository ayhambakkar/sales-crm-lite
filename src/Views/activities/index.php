<section>
    <?php
        $buildUrl = static function (array $overrides = []) use ($filters, $pagination, $currentUser): string {
            $query = [
                'entity_type' => $filters['entity_type'] ?? '',
                'action' => $filters['action'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'page' => $pagination['page'] ?? 1,
            ];

            if (($currentUser['role'] ?? '') === 'admin') {
                $query['user_id'] = $filters['user_id'] ?? '';
            }

            $query = array_merge($query, $overrides);

            foreach ($query as $key => $value) {
                if ($value === '' || $value === null || ($key === 'page' && (int) $value <= 1)) {
                    unset($query[$key]);
                }
            }

            return '/activities' . ($query === [] ? '' : '?' . http_build_query($query));
        };

        $pageTitle = 'Activity Log';
        $pageDescription = ($currentUser['role'] ?? '') === 'admin'
            ? 'Audit trail for important CRM actions.'
            : 'Your activity and activity on CRM records assigned to you.';
        $pageActions = '<a href="/exports/activities.csv" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Export CSV</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <form method="GET" action="/activities" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-5">
            <div>
                <label for="entity_type" class="mb-1.5 block text-sm font-medium text-slate-700">Entity</label>
                <select id="entity_type" name="entity_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All entities</option>
                    <?php foreach ($entityTypes as $entityType): ?>
                        <option value="<?= e($entityType) ?>" <?= ($filters['entity_type'] ?? '') === $entityType ? 'selected' : '' ?>>
                            <?= e(\App\Models\ActivityModel::entityLabel($entityType)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="action" class="mb-1.5 block text-sm font-medium text-slate-700">Action</label>
                <select id="action" name="action" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All actions</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= e($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>>
                            <?= e(\App\Models\ActivityModel::actionLabel($action)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                <div>
                    <label for="user_id" class="mb-1.5 block text-sm font-medium text-slate-700">Actor</label>
                    <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">All users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int) $user['id'] ?>" <?= (int) ($filters['user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>>
                                <?= e($user['first_name'] . ' ' . $user['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label for="date_from" class="mb-1.5 block text-sm font-medium text-slate-700">From</label>
                <input id="date_from" name="date_from" type="date" value="<?= e($filters['date_from'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label for="date_to" class="mb-1.5 block text-sm font-medium text-slate-700">To</label>
                <input id="date_to" name="date_to" type="date" value="<?= e($filters['date_to'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Apply</button>
            <?php if (! empty($hasActiveFilters)): ?>
                <a href="/activities" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
            <?php endif; ?>
        </div>
    </form>

    <p class="mb-3 text-sm text-slate-500">
        Showing page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>,
        <?= (int) $pagination['total'] ?> total activit<?= (int) $pagination['total'] === 1 ? 'y' : 'ies' ?>.
    </p>

    <?php if (empty($activities)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-base font-semibold text-slate-950"><?= ! empty($hasActiveFilters) ? 'No activities match these filters' : 'No activity has been recorded yet' ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?= ! empty($hasActiveFilters) ? 'Adjust the filters to broaden the audit trail.' : 'Important CRM actions will appear here once users start working.' ?></p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Activity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Entity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                            <?= e(\App\Models\ActivityModel::actionLabel((string) $activity['action'])) ?>
                                        </span>
                                    </div>
                                    <p class="mt-2 max-w-md text-slate-700"><?= e($activity['description'] ?? '') ?></p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                    <?= e(\App\Models\ActivityModel::entityLabel((string) $activity['entity_type'])) ?>
                                    <?php if (! empty($activity['entity_id'])): ?>
                                        #<?= (int) $activity['entity_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <p class="font-medium text-slate-700"><?= e($activity['actor_name'] ?: 'System') ?></p>
                                    <?php if (! empty($activity['actor_email'])): ?>
                                        <p class="text-xs text-slate-500"><?= e($activity['actor_email']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($activity['created_at']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <a href="/activities/<?= (int) $activity['id'] ?>" class="rounded-md border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
                $paginationLabel = 'Activity pages';
                include APP_ROOT . '/src/Views/partials/pagination.php';
            ?>
        </div>
    <?php endif; ?>
</section>
