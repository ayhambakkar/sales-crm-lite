<section>
    <?php
        $pageTitle = 'Users';
        $pageDescription = 'Manage administrator and sales representative accounts.';
        $pageActions = '<a href="/users/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create User</a>';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <?php if (empty($users)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-base font-semibold text-slate-950">No users found</h2>
            <p class="mt-1 text-sm text-slate-500">Create the first team account to start assigning CRM records.</p>
            <a href="/users/create" class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Create User</a>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Last Login</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-950"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($user['email']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = $user['role'];
                                        $badgeLabel = $user['role'] === 'admin' ? 'Admin' : 'Sales Rep';
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <?php
                                        $badgeValue = (int) $user['is_active'] === 1 ? 'active' : 'inactive';
                                        $badgeLabel = (int) $user['is_active'] === 1 ? 'Active' : 'Inactive';
                                        include APP_ROOT . '/src/Views/partials/status-badge.php';
                                    ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($user['last_login_at'] ?? 'Never') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <a href="/users/<?= (int) $user['id'] ?>/edit" class="rounded-md border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>

                                        <?php if ((int) $user['is_active'] === 1): ?>
                                            <form method="POST" action="/users/<?= (int) $user['id'] ?>/deactivate">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50" <?= (int) $user['id'] === (int) $currentUserId ? 'disabled' : '' ?>>
                                                    Deactivate
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/users/<?= (int) $user['id'] ?>/activate">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="rounded-md border border-emerald-200 px-3 py-1.5 font-medium text-emerald-700 hover:bg-emerald-50">Activate</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
