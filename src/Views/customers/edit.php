<section>
    <header>
        <h1>Edit Customer</h1>
        <p><a href="/customers/<?= (int) $customer['id'] ?>">Back to Customer</a></p>
    </header>

    <form method="POST" action="/customers/<?= (int) $customer['id'] ?>/edit" novalidate>
        <?= csrf_field() ?>
        <?php include APP_ROOT . '/src/Views/customers/form.php'; ?>
        <button type="submit">Save Changes</button>
    </form>
</section>
