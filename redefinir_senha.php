<?php
/* US1 — Define nova senha a partir do token recebido por e-mail */
require __DIR__ . '/includes/bootstrap.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$reset = $token !== '' ? db_one('SELECT * FROM password_resets WHERE token_hash = ? AND usado = 0 AND expira_em > NOW()', [hash('sha256', $token)]) : null;

if (!$reset) {
    flash('Link inválido ou expirado. Solicite uma nova recuperação.', 'err');
    redirect('recuperar_senha.php');
}

if (is_post()) {
    $s1 = (string) ($_POST['senha'] ?? '');
    $s2 = (string) ($_POST['senha2'] ?? '');
    if ($msg = senha_forte($s1)) {
        flash($msg, 'err');
    } elseif ($s1 !== $s2) {
        flash('A confirmação de senha não confere.', 'err');
    } else {
        db_exec('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($s1, PASSWORD_DEFAULT), $reset['usuario_id']]);
        db_exec('UPDATE password_resets SET usado = 1 WHERE usuario_id = ?', [$reset['usuario_id']]);
        flash('Senha alterada com sucesso. Faça login com a nova senha.', 'ok');
        redirect('login.php');
    }
}

auth_start('Nova senha', 'Criar nova senha');
?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="field"><label for="senha">Nova senha</label>
        <div class="control"><?= icon('lock', 18) ?><input type="password" id="senha" name="senha" placeholder="Crie uma senha forte" required>
          <button type="button" class="eye" data-toggle-pass aria-label="Mostrar senha"><?= icon('eye', 18) ?></button></div>
        <p class="hint">Mínimo de 8 caracteres, com maiúscula, minúscula, número e caractere especial.</p></div>
      <div class="field"><label for="senha2">Confirmar senha</label>
        <div class="control"><?= icon('lock', 18) ?><input type="password" id="senha2" name="senha2" required></div></div>
      <button class="btn" type="submit">Salvar nova senha</button>
    </form>
<?php auth_end();
