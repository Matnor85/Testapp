<?php $pageTitle = 'Byt PIN-kod'; ?>
<?php require __DIR__ . '/layout.php'; ?>

<h1>Byt PIN-kod</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 440px;">
    <form method="POST" action="/index.php?page=change_pin">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="current_pin">Nuvarande PIN-kod</label>
            <input type="password" id="current_pin" name="current_pin" required maxlength="10">
        </div>

        <div class="form-group">
            <label for="new_pin">Ny PIN-kod</label>
            <input type="password" id="new_pin" name="new_pin" required maxlength="10" minlength="4">
        </div>

        <div class="form-group">
            <label for="confirm_pin">Bekräfta ny PIN-kod</label>
            <input type="password" id="confirm_pin" name="confirm_pin" required maxlength="10" minlength="4">
        </div>

        <button type="submit" class="btn btn-primary">Byt PIN-kod</button>
        <a href="/index.php?page=dashboard" class="btn btn-secondary" style="margin-left: 0.5rem;">Avbryt</a>
    </form>
</div>

<?php require __DIR__ . '/layout_footer.php'; ?>
