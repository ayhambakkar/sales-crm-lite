<section>
    <header>
        <h1>Edit User</h1>
        <p><a href="/users">Back to Users</a></p>
    </header>

    <p>Status: <?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?></p>

    <form method="POST" action="/users/<?= (int) $user['id'] ?>/edit" novalidate>
        <?= csrf_field() ?>

        <div>
            <label for="first_name">First Name</label>
            <input id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required maxlength="100">
            <?php if (! empty($errors['first_name'])): ?>
                <p><?= e($errors['first_name']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="last_name">Last Name</label>
            <input id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required maxlength="100">
            <?php if (! empty($errors['last_name'])): ?>
                <p><?= e($errors['last_name']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
            <?php if (! empty($errors['email'])): ?>
                <p><?= e($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role) ?>" <?= ($user['role'] ?? '') === $role ? 'selected' : '' ?>>
                        <?= e($role === 'admin' ? 'Admin' : 'Sales Rep') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (! empty($errors['role'])): ?>
                <p><?= e($errors['role']) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit">Save Changes</button>
    </form>

    <hr>

    <form method="POST" action="/users/<?= (int) $user['id'] ?>/reset-password" novalidate>
        <?= csrf_field() ?>

        <div>
            <label for="password">New Password</label>
            <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
            <?php if (! empty($passwordErrors['password'])): ?>
                <p><?= e($passwordErrors['password']) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <hr>

    <?php if ((int) $user['is_active'] === 1): ?>
        <form method="POST" action="/users/<?= (int) $user['id'] ?>/deactivate">
            <?= csrf_field() ?>
            <button type="submit" <?= (int) $user['id'] === (int) $currentUserId ? 'disabled' : '' ?>>
                Deactivate User
            </button>
        </form>
    <?php else: ?>
        <form method="POST" action="/users/<?= (int) $user['id'] ?>/activate">
            <?= csrf_field() ?>
            <button type="submit">Activate User</button>
        </form>
    <?php endif; ?>
</section>
