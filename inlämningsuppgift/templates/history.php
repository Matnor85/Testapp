<?php $pageTitle = 'Transaktionshistorik'; ?>
<?php require __DIR__ . '/layout.php'; ?>

<h1>Transaktionshistorik</h1>

<div class="card">
    <!-- Kontoväljare -->
    <form method="GET" action="/index.php" style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="page" value="history">
        <div>
            <label for="account_id" style="display: block; font-size: 0.8rem; color: var(--muted); margin-bottom: 6px;">Konto</label>
            <select name="account_id" id="account_id" style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); color: var(--text); padding: 8px 12px;">
                <?php foreach ($accounts as $acc): ?>
                <option value="<?= e($acc['id']) ?>"
                    <?= ($selectedAccountId == $acc['id']) ? 'selected' : '' ?>>
                    <?= e(match($acc['account_type']) {
                        'checking' => 'Lönekonto',
                        'savings'  => 'Sparkonto',
                        'fixed'    => 'Fasträntekonto',
                        'credit'   => 'Kreditkonto',
                        default    => $acc['account_type'],
                    }) ?>
                    #<?= e($acc['id']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary" style="font-size: 0.85rem; padding: 8px 16px;">Visa</button>
    </form>

    <?php if (empty($transactions)): ?>
        <p style="color: var(--muted); text-align: center; padding: 2rem;">Inga transaktioner hittades.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Typ</th>
                <th>Belopp</th>
                <th>Från konto</th>
                <th>Till konto</th>
                <th>Datum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $tx): ?>
            <tr>
                <td>
                    <span class="badge badge-<?= e($tx['type']) ?>">
                        <?= e(tx_type_label($tx['type'])) ?>
                    </span>
                </td>
                <td><?= format_money((float)$tx['amount']) ?></td>
                <td style="color: var(--muted);"><?= $tx['from_account_id'] ? '#' . e($tx['from_account_id']) : '—' ?></td>
                <td style="color: var(--muted);"><?= $tx['to_account_id']   ? '#' . e($tx['to_account_id'])   : '—' ?></td>
                <td style="color: var(--muted);"><?= format_date($tx['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginering -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=history&account_id=<?= e($selectedAccountId) ?>&p=<?= $currentPage - 1 ?>">← Föregående</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $currentPage): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=history&account_id=<?= e($selectedAccountId) ?>&p=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=history&account_id=<?= e($selectedAccountId) ?>&p=<?= $currentPage + 1 ?>">Nästa →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/layout_footer.php'; ?>
