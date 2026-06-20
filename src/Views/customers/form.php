<div class="grid gap-5 md:grid-cols-2">
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="md:col-span-2">
            <label for="assigned_user_id" class="mb-1.5 block text-sm font-medium text-slate-700">Assigned User</label>
            <select id="assigned_user_id" name="assigned_user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Unassigned</option>
                <?php foreach ($assignees as $assignee): ?>
                    <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($customer['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
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
        <input id="first_name" name="first_name" value="<?= e($customer['first_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['first_name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['first_name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="last_name" class="mb-1.5 block text-sm font-medium text-slate-700">Last Name</label>
        <input id="last_name" name="last_name" value="<?= e($customer['last_name'] ?? '') ?>" required maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['last_name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['last_name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="company" class="mb-1.5 block text-sm font-medium text-slate-700">Company</label>
        <input id="company" name="company" value="<?= e($customer['company'] ?? '') ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['company'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['company']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
        <input id="email" type="email" name="email" value="<?= e($customer['email'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['email'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
        <input id="phone" name="phone" value="<?= e($customer['phone'] ?? '') ?>" maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['phone'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['phone']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="customer_status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
        <select id="customer_status" name="customer_status" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= ($customer['customer_status'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= e(ucfirst($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['customer_status'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['customer_status']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Address</label>
        <input id="address" name="address" value="<?= e($customer['address'] ?? '') ?>" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['address'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['address']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="city" class="mb-1.5 block text-sm font-medium text-slate-700">City</label>
        <input id="city" name="city" value="<?= e($customer['city'] ?? '') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['city'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['city']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="postal_code" class="mb-1.5 block text-sm font-medium text-slate-700">Postal Code</label>
        <input id="postal_code" name="postal_code" value="<?= e($customer['postal_code'] ?? '') ?>" maxlength="30" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['postal_code'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['postal_code']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="country" class="mb-1.5 block text-sm font-medium text-slate-700">Country</label>
        <input id="country" name="country" value="<?= e($customer['country'] ?? '') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['country'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['country']) ?></p>
        <?php endif; ?>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><?= e($customer['notes'] ?? '') ?></textarea>
    </div>
</div>
