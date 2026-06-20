<div class="grid gap-5 md:grid-cols-2">
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="md:col-span-2">
            <label for="assigned_user_id" class="mb-1.5 block text-sm font-medium text-slate-700">Assigned User</label>
            <select id="assigned_user_id" name="assigned_user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Unassigned</option>
                <?php foreach ($assignees as $assignee): ?>
                    <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($lead['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
                        <?= e($assignee['first_name'] . ' ' . $assignee['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (! empty($errors['assigned_user_id'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($errors['assigned_user_id']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div>
        <label for="first_name" class="mb-1.5 block text-sm font-medium text-slate-700">First Name</label>
        <input id="first_name" name="first_name" value="<?= e($lead['first_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['first_name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['first_name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="last_name" class="mb-1.5 block text-sm font-medium text-slate-700">Last Name</label>
        <input id="last_name" name="last_name" value="<?= e($lead['last_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['last_name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['last_name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="company" class="mb-1.5 block text-sm font-medium text-slate-700">Company</label>
        <input id="company" name="company" value="<?= e($lead['company'] ?? '') ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['company'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['company']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
        <input id="email" type="email" name="email" value="<?= e($lead['email'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['email'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
        <input id="phone" name="phone" value="<?= e($lead['phone'] ?? '') ?>" maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['phone'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['phone']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="source" class="mb-1.5 block text-sm font-medium text-slate-700">Source</label>
        <input id="source" name="source" value="<?= e($lead['source'] ?? '') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['source'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['source']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= ($lead['status'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= e(ucwords(str_replace('_', ' ', $status))) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['status'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['status']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="priority" class="mb-1.5 block text-sm font-medium text-slate-700">Priority</label>
        <select id="priority" name="priority" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php foreach ($priorities as $priority): ?>
                <option value="<?= e($priority) ?>" <?= ($lead['priority'] ?? '') === $priority ? 'selected' : '' ?>>
                    <?= e(ucfirst($priority)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['priority'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['priority']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="estimated_value" class="mb-1.5 block text-sm font-medium text-slate-700">Estimated Value</label>
        <input id="estimated_value" name="estimated_value" value="<?= e($lead['estimated_value'] ?? '') ?>" inputmode="decimal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['estimated_value'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['estimated_value']) ?></p>
        <?php endif; ?>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><?= e($lead['notes'] ?? '') ?></textarea>
    </div>
</div>
