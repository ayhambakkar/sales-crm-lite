<section>
    <header>
        <h1>Create Lead</h1>
        <p><a href="/leads">Back to Leads</a></p>
    </header>

    <form method="POST" action="/leads" novalidate>
        <?= csrf_field() ?>
        <?php include APP_ROOT . '/src/Views/leads/form.php'; ?>
        <button type="submit">Create Lead</button>
    </form>
</section>
