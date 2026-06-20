<section>
    <header>
        <h1><?= e($lead['first_name'] . ' ' . $lead['last_name']) ?></h1>
        <p>
            <a href="/leads">Back to Leads</a>
            |
            <a href="/leads/<?= (int) $lead['id'] ?>/edit">Edit</a>
        </p>
    </header>

    <dl>
        <dt>Company</dt>
        <dd><?= e($lead['company'] ?? '') ?></dd>

        <dt>Email</dt>
        <dd><?= e($lead['email'] ?? '') ?></dd>

        <dt>Phone</dt>
        <dd><?= e($lead['phone'] ?? '') ?></dd>

        <dt>Source</dt>
        <dd><?= e($lead['source'] ?? '') ?></dd>

        <dt>Status</dt>
        <dd><?= e(ucwords(str_replace('_', ' ', $lead['status']))) ?></dd>

        <dt>Priority</dt>
        <dd><?= e(ucfirst($lead['priority'])) ?></dd>

        <dt>Estimated Value</dt>
        <dd><?= $lead['estimated_value'] !== null ? e(number_format((float) $lead['estimated_value'], 2)) : '' ?></dd>

        <dt>Assigned To</dt>
        <dd><?= e($lead['assigned_to'] ?? 'Unassigned') ?></dd>

        <dt>Notes</dt>
        <dd><?= nl2br(e($lead['notes'] ?? '')) ?></dd>

        <dt>Created</dt>
        <dd><?= e($lead['created_at']) ?></dd>

        <dt>Updated</dt>
        <dd><?= e($lead['updated_at']) ?></dd>
    </dl>

    <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/delete">
        <?= csrf_field() ?>
        <button type="submit">Delete Lead</button>
    </form>
</section>
