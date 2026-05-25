<?php $pageTitle = 'Dashboard'; ?>
<?php require __DIR__ . '/layout.php'; ?>

<h1>Välkommen, <?= e($_SESSION['user_name']) ?></h1>

<!-- Kontokort -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <?php foreach ($accounts as $acc): ?>
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                    <?= e(match($acc['account_type']) {
                        'checking' => 'Lönekonto',
                        'savings'  => 'Sparkonto',
                        'fixed'    => 'Fasträntekonto',
                        'credit'   => 'Kreditkonto',
                        default    => $acc['account_type'],
                    }) ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--muted);">Konto #<?= e($acc['id']) ?></div>
            </div>
            <div style="width: 36px; height: 36px; background: rgba(0,184,212,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 1.1rem;">
                <?= $acc['account_type'] === 'credit' ? '💳' : '🏦' ?>
            </div>
        </div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text);">
            <?= format_money((float)$acc['balance']) ?>
        </div>
        <?php if ($acc['interest_rate'] > 0): ?>
        <div style="font-size: 0.78rem; color: var(--muted); margin-top: 6px;">
            Ränta: <?= e($acc['interest_rate']) ?>%
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Snabblänkar -->
<div class="card">
    <h2>Snabbval</h2>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="/index.php?page=withdraw" class="btn btn-primary">Uttag</a>
        <a href="/index.php?page=deposit"  class="btn btn-primary">Insättning</a>
        <a href="/index.php?page=transfer" class="btn btn-primary">Överföring</a>
        <a href="/index.php?page=history"  class="btn btn-secondary">Transaktionshistorik</a>
        <a href="/index.php?page=change_pin" class="btn btn-secondary">Byt PIN-kod</a>
    </div>
</div>

<!-- Senaste transaktioner -->
<?php if (!empty($recentTransactions)): ?>
<div class="card">
    <h2>Senaste transaktioner</h2>
    <table>
        <thead>
            <tr>
                <th>Typ</th>
                <th>Belopp</th>
                <th>Datum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentTransactions as $tx): ?>
            <tr>
                <td>
                    <span class="badge badge-<?= e($tx['type']) ?>">
                        <?= e(tx_type_label($tx['type'])) ?>
                    </span>
                </td>
                <td><?= format_money((float)$tx['amount']) ?></td>
                <td style="color: var(--muted);"><?= format_date($tx['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-top: 1rem;">
        <a href="/index.php?page=history" class="btn btn-secondary" style="font-size: 0.85rem;">Visa all historik →</a>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/layout_footer.php'; ?>
