<?php
    $navClass = ! empty($active)
        ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100'
        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950';
?>

<a href="<?= e($href) ?>" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium <?= e($navClass) ?>">
    <?= e($label) ?>
</a>
