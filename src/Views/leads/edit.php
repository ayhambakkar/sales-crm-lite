<section>
    <header>
        <h1>Edit Lead</h1>
        <p><a href="/leads/<?= (int) $lead['id'] ?>">Back to Lead</a></p>
    </header>

    <form method="POST" action="/leads/<?= (int) $lead['id'] ?>/edit" novalidate>
        <?= csrf_field() ?>
        <?php include APP_ROOT . '/src/Views/leads/form.php'; ?>
        <button type="submit">Save Changes</button>
    </form>
</section>
