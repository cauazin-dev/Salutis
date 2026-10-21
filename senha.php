<?php
/* Alteração de senha para qualquer perfil autenticado */
require __DIR__ . '/includes/bootstrap.php';
$u = exigir_login();

if (is_post()) {
    $atual = (string) ($_POST['atual'] ?? '');
    $s1    = (string) ($_POST['senha'] ?? '');
    $s2    = (string) ($_POST['senha2'] ?? '');
    $hash  = db_val('SELECT senha_hash FROM usuarios WHERE id = ?', [$u['id']]);
    if (!password_verify($atual, (string) $hash)) {
        flash('Senha atual incorreta.', 'err');
    } elseif ($msg = senha_forte($s1)) {
        flash($msg, 'err');
    } elseif ($s1 !== $s2) {
        flash('A confirmação de senha não confere.', 'err');
    } else {
        db_exec('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($s1, PASSWORD_DEFAULT), $u['id']]);
        flash('Senha alterada com sucesso.', 'ok');
        redirect('senha.php');
    }
}

page_start('Alterar senha', ['back' => dashboard_url($u['perfil']), 'h1' => 'Alterar senha']);
?>
<div class="card" style="max-width:480px">
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label for="atual">Senha atual</label><input type="password" id="atual" name="atual" required></div>
    <div class="field"><label for="senha">Nova senha</label><input type="password" id="senha" name="senha" required>
      <p class="hint">Mínimo de 8 caracteres, com maiúscula, minúscula, número e caractere especial.</p></div>
    <div class="field"><label for="senha2">Confirmar nova senha</label><input type="password" id="senha2" name="senha2" required></div>
    <button class="btn" type="submit">Salvar senha</button>
  </form>
</div>
<?php page_end();
