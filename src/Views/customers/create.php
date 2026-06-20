<section>
    <header>
        <h1>Create Customer</h1>
        <p><a href="/customers">Back to Customers</a></p>
    </header>

    <form method="POST" action="/customers" novalidate>
        <?= csrf_field() ?>
        <?php include APP_ROOT . '/src/Views/customers/form.php'; ?>
        <button type="submit">Create Customer</button>
    </form>
</section>
