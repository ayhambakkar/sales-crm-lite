<?php if ((int) ($pagination['total_pages'] ?? 1) > 1): ?>
    <nav class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between" aria-label="<?= e($paginationLabel ?? 'Pages') ?>">
        <p class="text-sm text-slate-500">
            Page <?= (int) $pagination['page'] ?> of <?= (int) $pagination['total_pages'] ?>
        </p>

        <div class="flex flex-wrap items-center gap-1">
            <?php if ((int) $pagination['page'] > 1): ?>
                <a class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50" href="<?= e($buildUrl(['page' => (int) $pagination['page'] - 1])) ?>">Previous</a>
            <?php endif; ?>

            <?php for ($page = 1; $page <= (int) $pagination['total_pages']; $page++): ?>
                <?php if ($page === (int) $pagination['page']): ?>
                    <span class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white"><?= $page ?></span>
                <?php else: ?>
                    <a class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50" href="<?= e($buildUrl(['page' => $page])) ?>"><?= $page ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ((int) $pagination['page'] < (int) $pagination['total_pages']): ?>
                <a class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50" href="<?= e($buildUrl(['page' => (int) $pagination['page'] + 1])) ?>">Next</a>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
