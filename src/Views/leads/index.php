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
    ?>

    <header>
        <h1>Leads</h1>
        <p>Track prospects and early sales opportunities.</p>
        <p><a href="/leads/create">Create Lead</a></p>
    </header>

    <form method="GET" action="/leads">
        <div>
            <label for="q">Search</label>
            <input
                id="q"
                name="q"
                value="<?= e($filters['q'] ?? '') ?>"
                placeholder="Name, company, email, or phone"
            >
        </div>

        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                        <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
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
                <label for="assigned_user_id">Assigned User</label>
                <select id="assigned_user_id" name="assigned_user_id">
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
            <label for="sort">Sort By</label>
            <select id="sort" name="sort">
                <option value="created_at" <?= ($sort['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>Created date</option>
                <option value="status" <?= ($sort['sort'] ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                <option value="priority" <?= ($sort['sort'] ?? '') === 'priority' ? 'selected' : '' ?>>Priority</option>
                <option value="estimated_value" <?= ($sort['sort'] ?? '') === 'estimated_value' ? 'selected' : '' ?>>Estimated value</option>
            </select>
        </div>

        <div>
            <label for="direction">Direction</label>
            <select id="direction" name="direction">
                <option value="desc" <?= ($sort['direction'] ?? '') === 'desc' ? 'selected' : '' ?>>Descending</option>
                <option value="asc" <?= ($sort['direction'] ?? '') === 'asc' ? 'selected' : '' ?>>Ascending</option>
            </select>
        </div>

        <button type="submit">Apply</button>

        <?php if (! empty($hasActiveFilters)): ?>
            <a href="/leads">Clear filters</a>
        <?php endif; ?>
    </form>

    <p>
        Showing page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>,
        <?= (int) $pagination['total'] ?> total lead<?= (int) $pagination['total'] === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($leads)): ?>
        <?php if (! empty($hasActiveFilters)): ?>
            <p>No leads match the current search or filters.</p>
        <?php else: ?>
            <p>No leads exist yet.</p>
        <?php endif; ?>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Estimated Value</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td>
                            <a href="/leads/<?= (int) $lead['id'] ?>">
                                <?= e($lead['first_name'] . ' ' . $lead['last_name']) ?>
                            </a>
                        </td>
                        <td><?= e($lead['company'] ?? '') ?></td>
                        <td>
                            <?= e(ucwords(str_replace('_', ' ', $lead['status']))) ?>
                            <?php if (! empty($lead['converted_customer_id'])): ?>
                                <br>
                                <a href="/customers/<?= (int) $lead['converted_customer_id'] ?>">View customer</a>
                            <?php endif; ?>
                        </td>
                        <td><?= e(ucfirst($lead['priority'])) ?></td>
                        <td><?= $lead['estimated_value'] !== null ? e(number_format((float) $lead['estimated_value'], 2)) : '' ?></td>
                        <td><?= e($lead['assigned_to'] ?? 'Unassigned') ?></td>
                        <td><?= e($lead['created_at']) ?></td>
                        <td>
                            <a href="/leads/<?= (int) $lead['id'] ?>/edit">Edit</a>
                            <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/delete" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ((int) $pagination['total_pages'] > 1): ?>
            <nav aria-label="Lead pages">
                <?php if ((int) $pagination['page'] > 1): ?>
                    <a href="<?= e($buildUrl(['page' => (int) $pagination['page'] - 1])) ?>">Previous</a>
                <?php endif; ?>

                <?php for ($page = 1; $page <= (int) $pagination['total_pages']; $page++): ?>
                    <?php if ($page === (int) $pagination['page']): ?>
                        <strong><?= $page ?></strong>
                    <?php else: ?>
                        <a href="<?= e($buildUrl(['page' => $page])) ?>"><?= $page ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ((int) $pagination['page'] < (int) $pagination['total_pages']): ?>
                    <a href="<?= e($buildUrl(['page' => (int) $pagination['page'] + 1])) ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
