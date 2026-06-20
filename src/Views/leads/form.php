<?php if (($currentUser['role'] ?? '') === 'admin'): ?>
    <div>
        <label for="assigned_user_id">Assigned User</label>
        <select id="assigned_user_id" name="assigned_user_id">
            <option value="">Unassigned</option>
            <?php foreach ($assignees as $assignee): ?>
                <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($lead['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
                    <?= e($assignee['first_name'] . ' ' . $assignee['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (! empty($errors['assigned_user_id'])): ?>
            <p><?= e($errors['assigned_user_id']) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div>
    <label for="first_name">First Name</label>
    <input id="first_name" name="first_name" value="<?= e($lead['first_name'] ?? '') ?>" required maxlength="100">
    <?php if (! empty($errors['first_name'])): ?>
        <p><?= e($errors['first_name']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="last_name">Last Name</label>
    <input id="last_name" name="last_name" value="<?= e($lead['last_name'] ?? '') ?>" required maxlength="100">
    <?php if (! empty($errors['last_name'])): ?>
        <p><?= e($errors['last_name']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="company">Company</label>
    <input id="company" name="company" value="<?= e($lead['company'] ?? '') ?>" maxlength="150">
    <?php if (! empty($errors['company'])): ?>
        <p><?= e($errors['company']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="<?= e($lead['email'] ?? '') ?>">
    <?php if (! empty($errors['email'])): ?>
        <p><?= e($errors['email']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="phone">Phone</label>
    <input id="phone" name="phone" value="<?= e($lead['phone'] ?? '') ?>" maxlength="50">
    <?php if (! empty($errors['phone'])): ?>
        <p><?= e($errors['phone']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="source">Source</label>
    <input id="source" name="source" value="<?= e($lead['source'] ?? '') ?>" maxlength="100">
    <?php if (! empty($errors['source'])): ?>
        <p><?= e($errors['source']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="status">Status</label>
    <select id="status" name="status" required>
        <?php foreach ($statuses as $status): ?>
            <option value="<?= e($status) ?>" <?= ($lead['status'] ?? '') === $status ? 'selected' : '' ?>>
                <?= e(ucwords(str_replace('_', ' ', $status))) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (! empty($errors['status'])): ?>
        <p><?= e($errors['status']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="priority">Priority</label>
    <select id="priority" name="priority" required>
        <?php foreach ($priorities as $priority): ?>
            <option value="<?= e($priority) ?>" <?= ($lead['priority'] ?? '') === $priority ? 'selected' : '' ?>>
                <?= e(ucfirst($priority)) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (! empty($errors['priority'])): ?>
        <p><?= e($errors['priority']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="estimated_value">Estimated Value</label>
    <input id="estimated_value" name="estimated_value" value="<?= e($lead['estimated_value'] ?? '') ?>" inputmode="decimal">
    <?php if (! empty($errors['estimated_value'])): ?>
        <p><?= e($errors['estimated_value']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="5"><?= e($lead['notes'] ?? '') ?></textarea>
</div>
