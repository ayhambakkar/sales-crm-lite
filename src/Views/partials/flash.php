<?php if (! empty($flashSuccess)): ?>
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
        <?= e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if (! empty($flashError)): ?>
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">
        <?= e($flashError) ?>
    </div>
<?php endif; ?>
