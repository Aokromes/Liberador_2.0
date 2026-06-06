<?php if (!isset($account)) exit; ?>

<ul class="account-info">

    <li>
        <strong><?php echo $lang['user']; ?>:</strong>
        <span class="private"><?php echo htmlspecialchars($account['username']); ?></span>
    </li>

    <li>
        <strong><?php echo $lang['email']; ?>:</strong>
        <span class="private"><?php echo htmlspecialchars($account['email']); ?></span>
    </li>

    <li>
        <strong><?php echo $lang['register_date']; ?>:</strong>
        <?php echo htmlspecialchars($account['joindate']); ?>
    </li>

    <li>
        <strong><?php echo $lang['last_ip']; ?>:</strong>
        <span class="private"><?php echo htmlspecialchars($account['last_ip']); ?></span>
    </li>

</ul>

<div class="account-buttons">

    <!-- Cerrar sesión -->
    <form method="POST">
        <button type="submit" name="logout"><?php echo $lang['logout']; ?></button>
    </form>

    <!-- Buscar cuentas con mi email -->
    <form method="POST">
        <input type="hidden" name="search_email" value="<?php echo htmlspecialchars($account['email']); ?>">
        <button type="submit"><?php echo $lang['search_email']; ?></button>
    </form>

</div>

