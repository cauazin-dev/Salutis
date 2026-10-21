<?php
/* US1 — Recuperação de senha: gera token (válido por 1h) e envia e-mail */
require __DIR__ . '/includes/bootstrap.php';

$link = null;
if (is_post()) {
    $email = strtolower(post('email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Informe um e-mail válido.', 'err');
    } else {
        $usr = db_one('SELECT id, nome FROM usuarios WHERE email = ? AND ativo = 1', [$email]);
        if ($usr) {
            $token = bin2hex(random_bytes(32));
            db_exec('UPDATE password_resets SET usado = 1 WHERE usuario_id = ?', [$usr['id']]);
            db_exec('INSERT INTO password_resets (usuario_id, token_hash, expira_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))', [$usr['id'], hash('sha256', $token)]);
            $link = url_abs('redefinir_senha.php?token=' . $token);
            enviar_email($email, 'Salutis — Recuperação de senha',
                "Olá, {$usr['nome']}.\n\nPara criar uma nova senha, acesse o link abaixo (válido por 1 hora):\n$link\n\nSe você não solicitou, ignore esta mensagem.");
        }
        // mesma resposta para e-mails existentes e inexistentes (evita enumeração de contas)
        flash('Se o e-mail estiver cadastrado, enviaremos as instruções de recuperação.', 'ok');
        if (!MODO_DEMO) $link = null;
    }
}

auth_start('Recuperar senha', 'Recuperar senha');
?>
    <p class="muted small mb">Informe o e-mail da sua conta para receber o link de redefinição.</p>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field"><label for="email">E-mail</label>
        <div class="control"><?= icon('mail', 18) ?><input type="email" id="email" name="email" placeholder="nome@exemplo.com" required></div></div>
      <button class="btn" type="submit">Enviar link</button>
    </form>
    <?php if ($link): ?>
      <div class="alert alert-warn mt small"><b>Modo demonstração:</b> o e-mail foi gravado em <code>storage/emails.log</code>. Link direto:<br><a href="<?= e($link) ?>">Redefinir senha</a></div>
    <?php endif; ?>
<?php auth_end('<a href="' . e(url('login.php')) . '">← Voltar ao login</a>');
