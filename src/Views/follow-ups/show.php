<section>
    <?php
        $isOverdue = \App\Models\FollowUpModel::isOverdue($followUp);
        $relatedUrl = ! empty($followUp['lead_id'])
            ? '/leads/' . (int) $followUp['lead_id']
            : '/customers/' . (int) $followUp['customer_id'];
        $relatedLabel = ! empty($followUp['lead_id'])
            ? ($followUp['lead_name'] ?? 'Lead #' . (int) $followUp['lead_id'])
            : ($followUp['customer_name'] ?? 'Customer #' . (int) $followUp['customer_id']);
        $relatedType = ! empty($followUp['lead_id']) ? 'Lead' : 'Customer';

        ob_start();
    ?>
        <a href="/follow-ups" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Follow-Ups</a>
        <a href="/follow-ups/<?= (int) $followUp['id'] ?>/edit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Edit</a>
    <?php
        $pageActions = ob_get_clean();
        $pageTitle = $followUp['title'];
        $pageDescription = 'Follow-up details, status, and related CRM record.';
        include APP_ROOT . '/src/Views/partials/page-header.php';
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1 flex flex-wrap gap-2">
                        <?php
                            $badgeValue = $followUp['status'];
                            include APP_ROOT . '/src/Views/partials/status-badge.php';
                        ?>
                        <?php if ($isOverdue): ?>
                            <?php
                                $badgeValue = 'overdue';
                                $badgeLabel = 'Overdue';
                                include APP_ROOT . '/src/Views/partials/status-badge.php';
                            ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</dt>
                    <dd class="mt-1">
                        <?php
                            $priorityValue = $followUp['priority'];
                            include APP_ROOT . '/src/Views/partials/priority-badge.php';
                        ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Due</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($followUp['due_at']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completed</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($followUp['completed_at'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Related <?= e($relatedType) ?></dt>
                    <dd class="mt-1 text-sm">
                        <a href="<?= e($relatedUrl) ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($relatedLabel) ?></a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($followUp['assigned_to'] ?? 'Unassigned') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($followUp['created_at']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Updated</dt>
                    <dd class="mt-1 text-sm text-slate-950"><?= e($followUp['updated_at']) ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-slate-700"><?= nl2br(e($followUp['description'] ?? '')) ?></dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4">
            <?php if ($followUp['status'] === 'open'): ?>
                <form method="POST" action="/follow-ups/<?= (int) $followUp['id'] ?>/done" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <?= csrf_field() ?>
                    <h2 class="text-base font-semibold text-emerald-950">Complete Follow-Up</h2>
                    <p class="mt-1 text-sm text-emerald-700">Mark this task as done and record completion time.</p>
                    <button type="submit" class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Mark Done</button>
                </form>

                <form method="POST" action="/follow-ups/<?= (int) $followUp['id'] ?>/cancel" class="rounded-xl border border-slate-200 bg-white p-5">
                    <?= csrf_field() ?>
                    <h2 class="text-base font-semibold text-slate-950">Cancel Follow-Up</h2>
                    <p class="mt-1 text-sm text-slate-500">Close this task without completion.</p>
                    <button type="submit" class="mt-4 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel Task</button>
                </form>
            <?php endif; ?>

            <form method="POST" action="/follow-ups/<?= (int) $followUp['id'] ?>/delete" class="rounded-xl border border-red-200 bg-white p-5">
                <?= csrf_field() ?>
                <h2 class="text-base font-semibold text-slate-950">Delete Follow-Up</h2>
                <p class="mt-1 text-sm text-slate-500">Remove this task permanently.</p>
                <button type="submit" class="mt-4 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete Follow-Up</button>
            </form>
        </div>
    </div>
</section>
