<?php $pageTitle = 'Admin – Kunddetaljer'; ?>
<?php require __DIR__ . '/../admin_layout.php'; ?>

<div style="margin-bottom: 2rem;">
    <a href="index.php?page=admin_accounts" style="color: var(--muted); text-decoration: none;">← Tillbaka till kundöversikt</a>
</div>

<?php if ($customer): ?>

    <h1 style="margin-bottom: 0.5rem;">Accounts for <?= e($customer['name']) ?></h1>
    <p style="color: var(--muted); margin-bottom: 2rem; font-size: 0.85rem;">
        User ID: #<?= e($customer['id']) ?> | Card Number: <?= e($customer['card_number']) ?>
    </p>

    <!-- Kontolista -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Account ID</th>
                    <th>Account Type</th>
                    <th style="text-align: right;">Balance</th>
                    <th style="text-align: center;">Interest Rate</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--muted);">
                            The customer has no accounts.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acc): ?>
                    <tr>
                        <td style="color: var(--muted);"><?= e($acc['id']) ?></td>
                        <td>
                            <span class="badge badge-user">
                                <?= e(match($acc['account_type']) {
                                    'checking' => 'Checking Account',
                                    'savings'  => 'Savings Account',
                                    'fixed'    => 'Fixed Interest Account',
                                    'credit'   => 'Credit Account',
                                    default    => $acc['account_type'],
                                }) ?>
                            </span>
                        </td>
                        <td style="text-align: right; color: var(--accent);">
                            <strong><?= format_money((float)$acc['balance']) ?></strong>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($acc['active']): ?>
                                <span class="status-active">● Active</span>
                            <?php else: ?>
                                <span class="status-frozen">● Frozen</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <form method="POST" action="index.php?page=admin_toggle_account" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="account_id"     value="<?= e($acc['id']) ?>">
                                <input type="hidden" name="user_id"        value="<?= e($customer['id']) ?>">
                                <input type="hidden" name="current_status" value="<?= e($acc['active']) ?>">
                                <button type="submit" style="background: none; border: none; color: var(--accent); font-family: monospace; cursor: pointer;">
                                    <?= $acc['active'] ? '[ Freeze ]' : '[ Activate ]' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Öppna nytt konto -->
    <div class="card">
        <h3>Open New Account</h3>

        <form method="POST" action="index.php?page=admin_customer_details&id=<?= e($customer['id']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id"      value="<?= e($customer['id']) ?>">
            <input type="hidden" name="admin_action" value="open_account">

            <div class="form-group">
                <label for="account_type">Account Type</label>
                <select id="account_type" name="account_type" required style="width: 100%; max-width: 300px;">
                <option value="checking">Checking Account (0%)</option>
                <option value="savings">Savings Account (2.5%)</option>
                <option value="fixed">Fixed Interest Account (4.0%)</option>
                <option value="credit">Credit Account (0%)</option>
            </select>
            </div>

            <div class="form-group">
                <label for="insertCash">Start Balance (SEK)</label>
                <input type="number" id="insertCash" name="insertCash"
                       min="0" step="1" placeholder="0" style="max-width: 300px;">
            </div>

            <button type="submit" class="btn btn-primary">Open Account</button>
        </form>
    </div>

    <!-- Transfer -->
    <div class="card">
        <h3>Transfer</h3>

        <form method="POST" action="index.php?page=admin_customer_details&id=<?= e($customer['id']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id"      value="<?= e($customer['id']) ?>">
            <input type="hidden" name="admin_action" value="admin_transfer">

            <div class="form-group">
                <label for="from_account_id">From Account</label>
                <select id="from_account_id" name="from_account_id" required style="max-width: 400px;">
                    <option value="">-- Select Account --</option>
                    <?php foreach ($accounts as $acc): ?>
                        <?php if (!$acc['active']) continue; ?>
                        <option value="<?= e($acc['id']) ?>">
                            <?= e(match($acc['account_type']) { 
                                'checking'=>'Checking account', 
                                'savings'=>'Savings account', 
                                'fixed'=>'Fixed account', 
                                'credit'=>'Credit account', 
                                default=>$acc['account_type'] 
                                }) ?>
                            — <?= format_money((float)$acc['balance']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="to_account_internal">To Account (Own)</label>
                <select id="to_account_internal" name="to_account_id_internal" style="max-width: 400px;">
                    <option value="">-- Select Account --</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= e($acc['id']) ?>">
                            <?= e(match($acc['account_type']) {
                                'checking'=>'Checking account', 
                                'savings'=>'Savings account', 
                                'fixed'=>'Fixed account', 
                                'credit'=>'Credit account', 
                                default=>$acc['account_type'] 
                                }) ?>
                            — <?= format_money((float)$acc['balance']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="to_account_external">Or to another account ID</label>
                <input type="number" id="to_account_external" name="to_account_id_external"
                       placeholder="Recipient's account ID" style="max-width: 400px;">
                <small>Leave blank if you have selected an own account above.</small>
            </div>

            <div class="form-group">
                <label for="transfer_amount">Amount (SEK)</label>
                <input type="number" id="transfer_amount" name="amount"
                       step="1" min="1" required placeholder="0" style="max-width: 400px;">
            </div>

            <button type="submit" class="btn btn-primary">Accept Transfer</button>
        </form>
    </div>

<?php else: ?>
    <div class="alert alert-error">User not found.</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout_footer.php'; ?>
