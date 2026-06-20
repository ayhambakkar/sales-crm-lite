<?php
    $priorityValue = (string) ($priorityValue ?? '');
    $priorityClasses = [
        'low' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'medium' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'high' => 'bg-red-50 text-red-700 ring-red-200',
    ];
    $priorityClass = $priorityClasses[$priorityValue] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
?>

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= e($priorityClass) ?>">
    <?= e(ucfirst($priorityValue)) ?>
</span>
<?php unset($priorityValue, $priorityClasses, $priorityClass); ?>
