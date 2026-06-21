<?php
    $activityItems = $activityItems ?? [];
    $activityTitle = $activityTitle ?? 'Recent Activity';
    $activityDescription = $activityDescription ?? '';
    $activityEmpty = $activityEmpty ?? 'No activity has been recorded yet.';
    $activityViewAllUrl = $activityViewAllUrl ?? '/activities';
?>

<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-950"><?= e($activityTitle) ?></h2>
            <?php if ($activityDescription !== ''): ?>
                <p class="mt-1 text-sm text-slate-500"><?= e($activityDescription) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= e($activityViewAllUrl) ?>" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View log</a>
    </div>

    <?php if (empty($activityItems)): ?>
        <div class="p-6 text-sm text-slate-500"><?= e($activityEmpty) ?></div>
    <?php else: ?>
        <div class="divide-y divide-slate-100">
            <?php foreach ($activityItems as $activity): ?>
                <a href="/activities/<?= (int) $activity['id'] ?>" class="block px-5 py-4 hover:bg-slate-50">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
                                    <?= e(\App\Models\ActivityModel::entityLabel((string) $activity['entity_type'])) ?>
                                </span>
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                    <?= e(\App\Models\ActivityModel::actionLabel((string) $activity['action'])) ?>
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-medium text-slate-950"><?= e($activity['description'] ?? 'Activity recorded') ?></p>
                            <p class="mt-1 text-xs text-slate-500">
                                <?= e($activity['actor_name'] ?: 'System') ?>
                                <?php if (! empty($activity['entity_id'])): ?>
                                    <span class="text-slate-300">/</span>
                                    <?= e(\App\Models\ActivityModel::entityLabel((string) $activity['entity_type'])) ?> #<?= (int) $activity['entity_id'] ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <p class="text-xs text-slate-500"><?= e($activity['created_at']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
