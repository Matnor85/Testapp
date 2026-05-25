<?php $pageTitle = 'Admin – Audit-logg'; ?>
<?php require __DIR__ . '/../admin_layout.php'; ?>

<h1>Audit-logg</h1>

<!-- Filter -->
<div class="card">
    <form method="GET" action="index.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="page" value="admin_audit_log">

        <div>
            <label>Event Type</label>
            <select name="type">
                <option value="">-- All Activities --</option>
                <option value="admin_login_success"  <?= ($filterType ?? '') === 'admin_login_success'  ? 'selected' : '' ?>>Login</option>
                <option value="admin_logout"         <?= ($filterType ?? '') === 'admin_logout'         ? 'selected' : '' ?>>Logout</option>
                <option value="admin_login_failed"   <?= ($filterType ?? '') === 'admin_login_failed'   ? 'selected' : '' ?>>Login failed</option>
                <option value="admin_login_denied"   <?= ($filterType ?? '') === 'admin_login_denied'   ? 'selected' : '' ?>>Login denied</option>
                <option value="admin_create_user"    <?= ($filterType ?? '') === 'admin_create_user'    ? 'selected' : '' ?>>Created User</option>
                <option value="admin_create_account" <?= ($filterType ?? '') === 'admin_create_account' ? 'selected' : '' ?>>Created Account</option>
                <option value="admin_delete_user"    <?= ($filterType ?? '') === 'admin_delete_user'    ? 'selected' : '' ?>>Deleted User</option>
                <option value="admin_delete_account" <?= ($filterType ?? '') === 'admin_delete_account' ? 'selected' : '' ?>>Deleted Account</option>
                <option value="ACCOUNT_LOCKED"       <?= ($filterType ?? '') === 'ACCOUNT_LOCKED'       ? 'selected' : '' ?>>Account Locked</option>
                <option value="ACCOUNT_UNLOCKED"     <?= ($filterType ?? '') === 'ACCOUNT_UNLOCKED'     ? 'selected' : '' ?>>Account Unlocked</option>
                <option value="TRANSFER"             <?= ($filterType ?? '') === 'TRANSFER'             ? 'selected' : '' ?>>Transfer Funds</option>
            </select>
        </div>

        <div>
            <label>Date From</label>
            <input type="date" name="date_from" value="<?= e($filterDateFrom ?? '') ?>">
        </div>

        <div>
            <label>Date To</label>
            <input type="date" name="date_to" value="<?= e($filterDateTo ?? '') ?>">
        </div>

        <div style="display: flex; gap: 0.5rem; align-self: flex-end;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index.php?page=admin_audit_log" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Event</th>
                <th>Details</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">
                        No log posts match the search criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="color: var(--muted);"><?= e($log['id']) ?></td>
                    <td><?= e($log['user_name'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge-user"><?= e($log['action']) ?></span>
                    </td>
                    <td style="color: var(--muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                        title="<?= e($log['description'] ?? '') ?>">
                        <?= e($log['description'] ?? '—') ?>
                    </td>
                    <td style="font-family: monospace; color: var(--muted);"><?= e($log['ip_address']) ?></td>
                    <td style="color: var(--muted);"><?= format_date($log['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginering -->
    <?php if ($totalPages > 1): ?>
    <?php $qs = '&type=' . urlencode($filterType ?? '') . '&date_from=' . urlencode($filterDateFrom ?? '') . '&date_to=' . urlencode($filterDateTo ?? ''); ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=admin_audit_log&p=1<?= $qs ?>"><<</a>
            <a href="index.php?page=admin_audit_log&p=<?= $currentPage - 1 ?><?= $qs ?>"><</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $currentPage): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="index.php?page=admin_audit_log&p=<?= $i ?><?= $qs ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="index.php?page=admin_audit_log&p=<?= $currentPage + 1 ?><?= $qs ?>">></a>
            <a href="?page=admin_audit_log&p=<?= $totalPages ?><?= $qs ?>">>></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
