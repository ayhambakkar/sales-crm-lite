<section>
    <?php
        $pageTitle = 'Edit User';
        $pageDescription = 'Update profile, role, status, or password.';
        $pageActions = '<a href="/users" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Users</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <div class="mb-5">
        <?php
            $badgeValue = (int) $user['is_active'] === 1 ? 'active' : 'inactive';
            $badgeLabel = (int) $user['is_active'] === 1 ? 'Active' : 'Inactive';
            include APP_ROOT . '/src/Views/partials/status-badge.php';
        ?>
    </div>

    <form method="POST" action="/users/<?= (int) $user['id'] ?>/edit" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
        <?= csrf_field() ?>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1.5 block text-sm font-medium text-slate-700">First Name</label>
                <input id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php if (! empty($errors['first_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['first_name']) ?></p>
            <?php endif; ?>
            </div>

            <div>
                <label for="last_name" class="mb-1.5 block text-sm font-medium text-slate-700">Last Name</label>
                <input id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php if (! empty($errors['last_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['last_name']) ?></p>
            <?php endif; ?>
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php if (! empty($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
            <?php endif; ?>
            </div>

            <div>
                <label for="role" class="mb-1.5 block text-sm font-medium text-slate-700">Role</label>
                <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role) ?>" <?= ($user['role'] ?? '') === $role ? 'selected' : '' ?>>
                        <?= e($role === 'admin' ? 'Admin' : 'Sales Rep') ?>
                    </option>
                <?php endforeach; ?>
                </select>
            <?php if (! empty($errors['role'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['role']) ?></p>
            <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Save Changes</button>
        </div>
    </form>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="/users/<?= (int) $user['id'] ?>/reset-password" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
            <?= csrf_field() ?>

            <h2 class="text-base font-semibold text-slate-950">Reset Password</h2>
            <p class="mt-1 text-sm text-slate-500">Set a new temporary password for this user.</p>

            <div class="mt-5">
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">New Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <?php if (! empty($passwordErrors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($passwordErrors['password']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="mt-5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset Password</button>
        </form>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-950">Account Status</h2>
            <p class="mt-1 text-sm text-slate-500">Deactivate or reactivate access without deleting history.</p>

            <?php if ((int) $user['is_active'] === 1): ?>
                <form method="POST" action="/users/<?= (int) $user['id'] ?>/deactivate" class="mt-5">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50" <?= (int) $user['id'] === (int) $currentUserId ? 'disabled' : '' ?>>
                        Deactivate User
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="/users/<?= (int) $user['id'] ?>/activate" class="mt-5">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Activate User</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
