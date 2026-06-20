<section>
    <header>
        <h1><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></h1>
        <p>
            <a href="/customers">Back to Customers</a>
            |
            <a href="/customers/<?= (int) $customer['id'] ?>/edit">Edit</a>
        </p>
    </header>

    <dl>
        <dt>Company</dt>
        <dd><?= e($customer['company'] ?? '') ?></dd>

        <dt>Status</dt>
        <dd><?= e(ucfirst($customer['customer_status'])) ?></dd>

        <dt>Email</dt>
        <dd><?= e($customer['email'] ?? '') ?></dd>

        <dt>Phone</dt>
        <dd><?= e($customer['phone'] ?? '') ?></dd>

        <dt>Address</dt>
        <dd><?= e($customer['address'] ?? '') ?></dd>

        <dt>City</dt>
        <dd><?= e($customer['city'] ?? '') ?></dd>

        <dt>Postal Code</dt>
        <dd><?= e($customer['postal_code'] ?? '') ?></dd>

        <dt>Country</dt>
        <dd><?= e($customer['country'] ?? '') ?></dd>

        <dt>Assigned To</dt>
        <dd><?= e($customer['assigned_to'] ?? 'Unassigned') ?></dd>

        <dt>Notes</dt>
        <dd><?= nl2br(e($customer['notes'] ?? '')) ?></dd>

        <dt>Created</dt>
        <dd><?= e($customer['created_at']) ?></dd>

        <dt>Updated</dt>
        <dd><?= e($customer['updated_at']) ?></dd>
    </dl>

    <form method="POST" action="/customers/<?= (int) $customer['id'] ?>/delete">
        <?= csrf_field() ?>
        <button type="submit">Delete Customer</button>
    </form>
</section>
