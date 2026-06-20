<section>
    <header>
        <h1>Leads</h1>
        <p>Track prospects and early sales opportunities.</p>
        <p><a href="/leads/create">Create Lead</a></p>
    </header>

    <?php if (empty($leads)): ?>
        <p>No leads found.</p>
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
                        <td><?= e(ucwords(str_replace('_', ' ', $lead['status']))) ?></td>
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
    <?php endif; ?>
</section>
