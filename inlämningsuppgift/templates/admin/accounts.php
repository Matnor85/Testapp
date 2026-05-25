<?php $pageTitle = 'Admin – Kundöversikt'; ?>
<?php require __DIR__ . '/../admin_layout.php'; ?>

<h1>Accounts Overview</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th><a href="index.php?page=admin_accounts&sort=owner_name&order=<?= $nextOrder ?>" style="color: var(--accent); text-decoration: none;">Customer ▲▼</a></th>
                <th style="text-align: center;"><a href="index.php?page=admin_accounts&sort=account_count&order=<?= $nextOrder ?>" style="color: var(--accent); text-decoration: none;">Account Count ▲▼</a></th>
                <th style="text-align: right;"><a href="index.php?page=admin_accounts&sort=balance&order=<?= $nextOrder ?>" style="color: var(--accent); text-decoration: none;">Total Balance ▲▼</a></th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $cust): ?>
            <tr>
                <td><strong><?= e($cust['owner_name']) ?></strong></td>
                
                <td style="text-align: center;">
                    <span class="badge badge-user"><?= e($cust['account_count']) ?> st</span>
                </td>
                
                <td style="text-align: right; color: var(--accent);">
                    <strong><?= format_money((float)($cust['total_balance'] ?? 0)) ?></strong>
                </td>
                
                <td style="text-align: center;">
                    <a href="index.php?page=admin_customer_details&id=<?= $cust['user_id'] ?>" class="btn-action" style="color: var(--accent); text-decoration: none;">
                        [ Show Accounts ]
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>