<section>
    <?php
        $metadata = [];

        if (! empty($activity['metadata'])) {
            $decoded = json_decode((string) $activity['metadata'], true);
            $metadata = is_array($decoded) ? $decoded : [];
        }

        ob_start();
    ?>
        <a href="/activities" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Activity Log</a>
    <?php
        $pageActions = ob_get_clean();
        $pageTitle = 'Activity #' . (int) $activity['id'];
        $pageDescription = 'Immutable audit entry details.';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entity</dt>
                    <dd class="mt-1 text-sm text-slate-950">
                        <?= e(\App\Models\ActivityModel::entityLabel((string) $activity['entity_type'])) ?>
                        <?php if (! empty($activity['entity_id'])): ?>
                            #<?= (int) $activity['entity_id'] ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Action</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                            <?= e(\App\Models\ActivityModel::actionLabel((string) $activity['action'])) ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Actor</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($activity['actor_name'] ?: 'System') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Actor Email</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($activity['actor_email'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($activity['created_at']) ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-slate-700"><?= nl2br(e($activity['description'] ?? '')) ?></dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-950">Metadata</h2>
            <?php if ($metadata === []): ?>
                <p class="mt-3 text-sm text-slate-500">No metadata was recorded for this activity.</p>
            <?php else: ?>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs leading-5 text-slate-100"><?= e(json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</section>
