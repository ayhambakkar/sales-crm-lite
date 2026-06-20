<section>
    <header>
        <h1>Users</h1>
        <p>Manage administrator and sales representative accounts.</p>
        <p><a href="/users/create">Create User</a></p>
    </header>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['role'] === 'admin' ? 'Admin' : 'Sales Rep') ?></td>
                        <td><?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                        <td><?= e($user['last_login_at'] ?? 'Never') ?></td>
                        <td>
                            <a href="/users/<?= (int) $user['id'] ?>/edit">Edit</a>

                            <?php if ((int) $user['is_active'] === 1): ?>
                                <form method="POST" action="/users/<?= (int) $user['id'] ?>/deactivate" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" <?= (int) $user['id'] === (int) $currentUserId ? 'disabled' : '' ?>>
                                        Deactivate
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/users/<?= (int) $user['id'] ?>/activate" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
