<div class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-normal text-slate-950"><?= e($pageTitle ?? '') ?></h1>
        <?php if (! empty($pageDescription)): ?>
            <p class="mt-1 text-sm text-slate-500"><?= e($pageDescription) ?></p>
        <?php endif; ?>
    </div>

    <?php if (! empty($pageActions)): ?>
        <div class="flex flex-wrap items-center gap-2">
            <?= $pageActions ?>
        </div>
    <?php endif; ?>
</div>
