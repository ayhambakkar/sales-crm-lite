<section>
    <?php
        $pageTitle = 'Create User';
        $pageDescription = 'Add an admin or sales representative account.';
        $pageActions = '<a href="/users" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Users</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <form method="POST" action="/users" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
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

            <div class="sm:col-span-2">
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php if (! empty($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['password']) ?></p>
            <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="/users" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create User</button>
        </div>
    </form>
</section>
