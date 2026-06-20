<section>
    <?php
        $buildUrl = static function (array $overrides = []) use ($filters, $sort, $pagination, $currentUser): string {
            $query = [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
                'overdue' => ! empty($filters['overdue']) ? '1' : '',
                'sort' => $sort['sort'] ?? 'due_at',
                'direction' => $sort['direction'] ?? 'asc',
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

            return '/follow-ups' . ($query === [] ? '' : '?' . http_build_query($query));
        };

        $pageTitle = 'Follow-Ups';
        $pageDescription = 'Track scheduled next steps for leads and customers.';
        $pageActions = '<a href="/follow-ups/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create Follow-Up</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <form method="GET" action="/follow-ups" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-700">Search</label>
                <input id="q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Title, description, related name, or company" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All statuses</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
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
                    <option value="due_at" <?= ($sort['sort'] ?? '') === 'due_at' ? 'selected' : '' ?>>Due date</option>
                    <option value="priority" <?= ($sort['sort'] ?? '') === 'priority' ? 'selected' : '' ?>>Priority</option>
                    <option value="status" <?= ($sort['sort'] ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                    <option value="created_at" <?= ($sort['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>Created date</option>
                </select>
            </div>

            <div>
                <label for="direction" class="mb-1.5 block text-sm font-medium text-slate-700">Direction</label>
                <select id="direction" name="direction" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="asc" <?= ($sort['direction'] ?? '') === 'asc' ? 'selected' : '' ?>>Ascending</option>
                    <option value="desc" <?= ($sort['direction'] ?? '') === 'desc' ? 'selected' : '' ?>>Descending</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="overdue" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?= ! empty($filters['overdue']) ? 'checked' : '' ?>>
                Overdue only
            </label>

            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Apply</button>
            <?php if (! empty($hasActiveFilters)): ?>
                <a href="/follow-ups" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
            <?php endif; ?>
        </div>
    </form>

    <p class="mb-3 text-sm text-slate-500">
        Showing page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>,
        <?= (int) $pagination['total'] ?> total follow-up<?= (int) $pagination['total'] === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($followUps)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-base font-semibold text-slate-950"><?= ! empty($hasActiveFilters) ? 'No follow-ups match these filters' : 'No follow-ups exist yet' ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?= ! empty($hasActiveFilters) ? 'Adjust the search or clear filters to broaden the result set.' : 'Create the first follow-up to track the next customer touch.' ?></p>
            <a href="/follow-ups/create" class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Create Follow-Up</a>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Task</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Related</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($followUps as $followUp): ?>
                            <?php
                                $isOverdue = \App\Models\FollowUpModel::isOverdue($followUp);
                                $relatedUrl = ! empty($followUp['lead_id'])
                                    ? '/leads/' . (int) $followUp['lead_id']
                                    : '/customers/' . (int) $followUp['customer_id'];
                                $relatedLabel = ! empty($followUp['lead_id'])
                                    ? ($followUp['lead_name'] ?? 'Lead #' . (int) $followUp['lead_id'])
                                    : ($followUp['customer_name'] ?? 'Customer #' . (int) $followUp['customer_id']);
                                $relatedCompany = ! empty($followUp['lead_id']) ? ($followUp['lead_company'] ?? '') : ($followUp['customer_company'] ?? '');
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <a href="/follow-ups/<?= (int) $followUp['id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($followUp['title']) ?></a>
                                    <?php if (! empty($followUp['description'])): ?>
                                        <p class="max-w-xs truncate text-xs text-slate-500"><?= e($followUp['description']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <a href="<?= e($relatedUrl) ?>" class="font-medium text-slate-700 hover:text-slate-950"><?= e($relatedLabel) ?></a>
                                    <p class="text-xs text-slate-500"><?= e($relatedCompany) ?></p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = $followUp['status'];
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                    <?php if ($isOverdue): ?>
                                        <?php
                                            $badgeValue = 'overdue';
                                            $badgeLabel = 'Overdue';
                                            include APP_ROOT . '/src/Views/partials/status-badge.php';
                                        ?>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $priorityValue = $followUp['priority'];
                                        include APP_ROOT . '/src/Views/partials/priority-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($followUp['due_at']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($followUp['assigned_to'] ?? 'Unassigned') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <a href="/follow-ups/<?= (int) $followUp['id'] ?>/edit" class="rounded-md border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                                        <?php if ($followUp['status'] === 'open'): ?>
                                            <form method="POST" action="/follow-ups/<?= (int) $followUp['id'] ?>/done">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="rounded-md border border-emerald-200 px-3 py-1.5 font-medium text-emerald-700 hover:bg-emerald-50">Done</button>
                                            </form>
                                            <form method="POST" action="/follow-ups/<?= (int) $followUp['id'] ?>/cancel">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="rounded-md border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
                $paginationLabel = 'Follow-up pages';
                include APP_ROOT . '/src/Views/partials/pagination.php';
            ?>
        </div>
    <?php endif; ?>
</section>
