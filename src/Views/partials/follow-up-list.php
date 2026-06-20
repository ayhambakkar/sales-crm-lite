<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Follow-Ups</h2>
            <p class="mt-1 text-sm text-slate-500">Scheduled next steps for this record.</p>
        </div>
        <a href="<?= e($createFollowUpUrl ?? '/follow-ups/create') ?>" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Create Follow-Up</a>
    </div>

    <?php if (empty($followUps)): ?>
        <div class="p-6 text-sm text-slate-500">No follow-ups are linked yet.</div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Task</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned To</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($followUps as $followUp): ?>
                        <?php $isOverdue = \App\Models\FollowUpModel::isOverdue($followUp); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <a href="/follow-ups/<?= (int) $followUp['id'] ?>" class="font-medium text-blue-700 hover:text-blue-900"><?= e($followUp['title']) ?></a>
                                <?php if (! empty($followUp['description'])): ?>
                                    <p class="max-w-xs truncate text-xs text-slate-500"><?= e($followUp['description']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
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
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <?php
                                    $priorityValue = $followUp['priority'];
                                    include APP_ROOT . '/src/Views/partials/priority-badge.php';
                                ?>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($followUp['due_at']) ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600"><?= e($followUp['assigned_to'] ?? 'Unassigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
