<?php if (($currentUser['role'] ?? '') === 'admin'): ?>
    <div>
        <label for="assigned_user_id">Assigned User</label>
        <select id="assigned_user_id" name="assigned_user_id">
            <option value="">Unassigned</option>
            <?php foreach ($assignees as $assignee): ?>
                <option value="<?= (int) $assignee['id'] ?>" <?= (int) ($customer['assigned_user_id'] ?? 0) === (int) $assignee['id'] ? 'selected' : '' ?>>
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
    <input id="first_name" name="first_name" value="<?= e($customer['first_name'] ?? '') ?>" required maxlength="100">
    <?php if (! empty($errors['first_name'])): ?>
        <p><?= e($errors['first_name']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="last_name">Last Name</label>
    <input id="last_name" name="last_name" value="<?= e($customer['last_name'] ?? '') ?>" required maxlength="100">
    <?php if (! empty($errors['last_name'])): ?>
        <p><?= e($errors['last_name']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="company">Company</label>
    <input id="company" name="company" value="<?= e($customer['company'] ?? '') ?>" maxlength="150">
    <?php if (! empty($errors['company'])): ?>
        <p><?= e($errors['company']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="<?= e($customer['email'] ?? '') ?>">
    <?php if (! empty($errors['email'])): ?>
        <p><?= e($errors['email']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="phone">Phone</label>
    <input id="phone" name="phone" value="<?= e($customer['phone'] ?? '') ?>" maxlength="50">
    <?php if (! empty($errors['phone'])): ?>
        <p><?= e($errors['phone']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="address">Address</label>
    <input id="address" name="address" value="<?= e($customer['address'] ?? '') ?>" maxlength="255">
    <?php if (! empty($errors['address'])): ?>
        <p><?= e($errors['address']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="city">City</label>
    <input id="city" name="city" value="<?= e($customer['city'] ?? '') ?>" maxlength="100">
    <?php if (! empty($errors['city'])): ?>
        <p><?= e($errors['city']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="postal_code">Postal Code</label>
    <input id="postal_code" name="postal_code" value="<?= e($customer['postal_code'] ?? '') ?>" maxlength="30">
    <?php if (! empty($errors['postal_code'])): ?>
        <p><?= e($errors['postal_code']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="country">Country</label>
    <input id="country" name="country" value="<?= e($customer['country'] ?? '') ?>" maxlength="100">
    <?php if (! empty($errors['country'])): ?>
        <p><?= e($errors['country']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="customer_status">Status</label>
    <select id="customer_status" name="customer_status" required>
        <?php foreach ($statuses as $status): ?>
            <option value="<?= e($status) ?>" <?= ($customer['customer_status'] ?? '') === $status ? 'selected' : '' ?>>
                <?= e(ucfirst($status)) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (! empty($errors['customer_status'])): ?>
        <p><?= e($errors['customer_status']) ?></p>
    <?php endif; ?>
</div>

<div>
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="5"><?= e($customer['notes'] ?? '') ?></textarea>
</div>
