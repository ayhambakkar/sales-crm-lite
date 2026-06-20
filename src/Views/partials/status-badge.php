<?php
    $badgeValue = (string) ($badgeValue ?? '');
    $badgeLabel = (string) ($badgeLabel ?? ucwords(str_replace('_', ' ', $badgeValue)));
    $badgeClasses = [
        'admin' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'sales_rep' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'inactive' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'vip' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'new' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'contacted' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'qualified' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'lost' => 'bg-red-50 text-red-700 ring-red-200',
        'converted' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'open' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'overdue' => 'bg-red-50 text-red-700 ring-red-200',
    ];
    $badgeClass = $badgeClasses[$badgeValue] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
?>

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= e($badgeClass) ?>">
    <?= e($badgeLabel) ?>
</span>
<?php unset($badgeValue, $badgeLabel, $badgeClasses, $badgeClass); ?>
