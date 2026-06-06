<?php global $lang, $langs; ?>

<?php if (!empty($message)): ?>
    <div class="error"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST" class="login-form">

    <div class="login-row">
        <label class="login-label">
            👤 <?php echo $lang['user']; ?>
        </label>
        <input type="text" name="username" class="login-input">
    </div>

    <div class="login-row">
        <label class="login-label">
            🔒 <?php echo $lang['pass']; ?>
        </label>
        <input type="password" name="password" class="login-input">
    </div>

    <button type="submit" class="login-button">
        <?php echo $lang['login']; ?>
    </button>

</form>

