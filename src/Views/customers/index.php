<section>
    <?php
        $buildUrl = static function (array $overrides = []) use ($filters, $sort, $pagination, $currentUser): string {
            $query = [
                'q' => $filters['q'] ?? '',
                'customer_status' => $filters['customer_status'] ?? '',
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

            return '/customers' . ($query === [] ? '' : '?' . http_build_query($query));
        };
    ?>

    <header>
        <h1>Customers</h1>
        <p>Manage customer accounts and ownership.</p>
        <p><a href="/customers/create">Create Customer</a></p>
    </header>

    <form method="GET" action="/customers">
        <div>
            <label for="q">Search</label>
            <input
                id="q"
                name="q"
                value="<?= e($filters['q'] ?? '') ?>"
                placeholder="Name, company, email, phone, or city"
            >
        </div>

        <div>
            <label for="customer_status">Status</label>
            <select id="customer_status" name="customer_status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['customer_status'] ?? '') === $status ? 'selected' : '' ?>>
                        <?= e(ucfirst($status)) ?>
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
                <option value="company" <?= ($sort['sort'] ?? '') === 'company' ? 'selected' : '' ?>>Company</option>
                <option value="customer_status" <?= ($sort['sort'] ?? '') === 'customer_status' ? 'selected' : '' ?>>Status</option>
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
            <a href="/customers">Clear filters</a>
        <?php endif; ?>
    </form>

    <p>
        Showing page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>,
        <?= (int) $pagination['total'] ?> total customer<?= (int) $pagination['total'] === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($customers)): ?>
        <?php if (! empty($hasActiveFilters)): ?>
            <p>No customers match the current search or filters.</p>
        <?php else: ?>
            <p>No customers exist yet.</p>
        <?php endif; ?>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <a href="/customers/<?= (int) $customer['id'] ?>">
                                <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            </a>
                        </td>
                        <td><?= e($customer['company'] ?? '') ?></td>
                        <td><?= e(ucfirst($customer['customer_status'])) ?></td>
                        <td><?= e($customer['email'] ?? '') ?></td>
                        <td><?= e($customer['phone'] ?? '') ?></td>
                        <td><?= e($customer['city'] ?? '') ?></td>
                        <td><?= e($customer['assigned_to'] ?? 'Unassigned') ?></td>
                        <td><?= e($customer['created_at']) ?></td>
                        <td>
                            <a href="/customers/<?= (int) $customer['id'] ?>/edit">Edit</a>
                            <form method="POST" action="/customers/<?= (int) $customer['id'] ?>/delete" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ((int) $pagination['total_pages'] > 1): ?>
            <nav aria-label="Customer pages">
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
