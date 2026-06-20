<?php
    $dueAtValue = '';
    if (! empty($followUp['due_at'])) {
        $timestamp = strtotime((string) $followUp['due_at']);
        $dueAtValue = $timestamp !== false ? date('Y-m-d\TH:i', $timestamp) : (string) $followUp['due_at'];
    }
?>

<div class="grid gap-5 md:grid-cols-2">
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="md:col-span-2">
            <label for="assigned_user_id" class="mb-1.5 block text-sm font-medium text-slate-700">Assigned User</label>
            <select id="assigned_user_id" name="assigned_user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Unassigned</option>
                <?php foreach ($assignees as $assignee): ?>
                    <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($followUp['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
                        <?= e($assignee['first_name'] . ' ' . $assignee['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (! empty($errors['assigned_user_id'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($errors['assigned_user_id']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="md:col-span-2">
        <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700">Title</label>
        <input id="title" name="title" value="<?= e($followUp['title'] ?? '') ?>" required maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['title'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="lead_id" class="mb-1.5 block text-sm font-medium text-slate-700">Related Lead</label>
        <select id="lead_id" name="lead_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <option value="">No lead</option>
            <?php foreach ($leadOptions as $lead): ?>
                <option value="<?= (int) $lead['id'] ?>" <?= (int) ($followUp['lead_id'] ?? 0) === (int) $lead['id'] ? 'selected' : '' ?>>
                    <?= e($lead['first_name'] . ' ' . $lead['last_name'] . (! empty($lead['company']) ? ' - ' . $lead['company'] : '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="customer_id" class="mb-1.5 block text-sm font-medium text-slate-700">Related Customer</label>
        <select id="customer_id" name="customer_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <option value="">No customer</option>
            <?php foreach ($customerOptions as $customer): ?>
                <option value="<?= (int) $customer['id'] ?>" <?= (int) ($followUp['customer_id'] ?? 0) === (int) $customer['id'] ? 'selected' : '' ?>>
                    <?= e($customer['first_name'] . ' ' . $customer['last_name'] . (! empty($customer['company']) ? ' - ' . $customer['company'] : '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (! empty($errors['parent'])): ?>
        <p class="md:col-span-2 text-sm text-red-600"><?= e($errors['parent']) ?></p>
    <?php endif; ?>

    <div>
        <label for="due_at" class="mb-1.5 block text-sm font-medium text-slate-700">Due Date</label>
        <input id="due_at" type="datetime-local" name="due_at" value="<?= e($dueAtValue) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        <?php if (! empty($errors['due_at'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['due_at']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="priority" class="mb-1.5 block text-sm font-medium text-slate-700">Priority</label>
        <select id="priority" name="priority" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php foreach ($priorities as $priority): ?>
                <option value="<?= e($priority) ?>" <?= ($followUp['priority'] ?? '') === $priority ? 'selected' : '' ?>>
                    <?= e(ucfirst($priority)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['priority'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['priority']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= ($followUp['status'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= e(ucfirst($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['status'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['status']) ?></p>
        <?php endif; ?>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><?= e($followUp['description'] ?? '') ?></textarea>
    </div>
</div>
