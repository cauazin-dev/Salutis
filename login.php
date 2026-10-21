<?php
/* US1 — Login com bloqueio temporário após tentativas falhas */
require __DIR__ . '/includes/bootstrap.php';

if ($u = usuario()) {
    redirect(dashboard_url($u['perfil']));
}

if (is_post()) {
    $email = strtolower(post('email'));
    $senha = (string) ($_POST['senha'] ?? '');
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '';
    $falhas = (int) db_val('SELECT COUNT(*) FROM login_tentativas WHERE email = ? AND ip = ? AND criada_em > DATE_SUB(NOW(), INTERVAL 10 MINUTE)', [$email, $ip]);

    if ($falhas >= 5) {
        flash('Muitas tentativas. Aguarde 10 minutos para tentar novamente.', 'err');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        flash('Informe um e-mail e uma senha válidos.', 'err');
    } else {
        $usr = db_one('SELECT * FROM usuarios WHERE email = ?', [$email]);
        if (!$usr || !password_verify($senha, $usr['senha_hash'])) {
            db_exec('INSERT INTO login_tentativas (email, ip) VALUES (?, ?)', [$email, $ip]);
            flash('E-mail ou senha inválidos.', 'err');
        } elseif (!$usr['ativo']) {
            flash('Conta desativada. Procure a administração da clínica.', 'err');
        } else {
            db_exec('DELETE FROM login_tentativas WHERE email = ?', [$email]);
            iniciar_sessao($usr);
            redirect(dashboard_url($usr['perfil']));
        }
    }
}

auth_start('Entrar', 'Entrar');
?>
    <form method="post" autocomplete="on" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">E-mail</label>
        <div class="control"><?= icon('mail', 18) ?><input type="email" id="email" name="email" placeholder="nome@exemplo.com" value="<?= e($_POST['email'] ?? '') ?>" required></div>
      </div>
      <div class="field">
        <label for="senha">Senha</label>
        <div class="control"><?= icon('lock', 18) ?><input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
          <button type="button" class="eye" data-toggle-pass aria-label="Mostrar senha"><?= icon('eye', 18) ?></button></div>
      </div>
      <p class="right small mb"><a href="<?= url('recuperar_senha.php') ?>">Esqueceu a senha?</a></p>
      <button class="btn" type="submit">Entrar</button>
    </form>
    <?php if (MODO_DEMO): ?>
    <details class="demo-box">
      <summary>Contas de demonstração</summary>
      <p class="mt">Paciente: <code>felipe@salutis.com</code> / <code>Paciente@123</code></p>
      <p>Médico: <code>erick@salutis.com</code> / <code>Medico@123</code></p>
      <p>Admin: <code>admin@salutis.com</code> / <code>Admin@123</code></p>
    </details>
    <?php endif; ?>
<?php auth_end('Não tem conta? <a href="' . e(url('cadastro.php')) . '">Cadastre-se</a>');
