<?php
/* US1 — Cadastro de paciente (e-mail válido, CPF válido, senha forte) */
require __DIR__ . '/includes/bootstrap.php';

if ($u = usuario()) {
    redirect(dashboard_url($u['perfil']));
}

$erros = [];
if (is_post()) {
    $nome  = post('nome');
    $email = strtolower(post('email'));
    $cpf   = so_digitos(post('cpf'));
    $tel   = post('numero');
    $s1    = (string) ($_POST['senha'] ?? '');
    $s2    = (string) ($_POST['senha2'] ?? '');

    if (mb_strlen($nome) < 5 || !str_contains($nome, ' ')) $erros[] = 'Informe seu nome completo.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (!valida_cpf($cpf)) $erros[] = 'CPF inválido.';
    if ($tel !== '' && strlen(so_digitos($tel)) < 10) $erros[] = 'Telefone inválido.';
    if ($msg = senha_forte($s1)) $erros[] = $msg;
    if ($s1 !== $s2) $erros[] = 'A confirmação de senha não confere.';
    if (!$erros) {
        if (db_val('SELECT id FROM usuarios WHERE email = ?', [$email])) $erros[] = 'Este e-mail já está cadastrado.';
        if (db_val('SELECT id FROM pacientes WHERE cpf = ?', [$cpf])) $erros[] = 'Este CPF já está cadastrado.';
    }
    if (!$erros) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            db_exec("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, 'paciente')", [$nome, $email, password_hash($s1, PASSWORD_DEFAULT)]);
            $uid = (int) $pdo->lastInsertId();
            db_exec('INSERT INTO pacientes (usuario_id, cpf, numero) VALUES (?, ?, ?)', [$uid, $cpf, $tel !== '' ? $tel : null]);
            $pdo->commit();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            exit('Erro ao criar a conta. Tente novamente.');
        }
        avisar($uid, 'Bem-vindo ao Salutis', 'Sua conta foi criada com sucesso. Agende sua primeira consulta pelo painel.', null);
        iniciar_sessao(['id' => $uid, 'nome' => $nome, 'email' => $email, 'perfil' => 'paciente']);
        flash('Conta criada com sucesso! Complete seu perfil quando quiser.', 'ok');
        redirect('paciente/dashboard.php');
    }
    foreach ($erros as $er) flash($er, 'err');
}

auth_start('Criar conta', 'Criar Conta');
?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field"><label for="nome">Nome completo</label>
        <div class="control"><?= icon('user', 18) ?><input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" value="<?= e($_POST['nome'] ?? '') ?>" required></div></div>
      <div class="field"><label for="email">E-mail</label>
        <div class="control"><?= icon('mail', 18) ?><input type="email" id="email" name="email" placeholder="nome@exemplo.com" value="<?= e($_POST['email'] ?? '') ?>" required></div></div>
      <div class="field"><label for="cpf">CPF</label>
        <div class="control"><?= icon('card', 18) ?><input type="text" id="cpf" name="cpf" data-mask="cpf" inputmode="numeric" placeholder="000.000.000-00" value="<?= e($_POST['cpf'] ?? '') ?>" required></div></div>
      <div class="field"><label for="numero">Telefone (opcional)</label>
        <div class="control"><?= icon('phone', 18) ?><input type="tel" id="numero" name="numero" data-mask="tel" placeholder="(61) 99999-9999" value="<?= e($_POST['numero'] ?? '') ?>"></div></div>
      <div class="field"><label for="senha">Senha</label>
        <div class="control"><?= icon('lock', 18) ?><input type="password" id="senha" name="senha" placeholder="Crie uma senha forte" required>
          <button type="button" class="eye" data-toggle-pass aria-label="Mostrar senha"><?= icon('eye', 18) ?></button></div>
        <p class="hint">Mínimo de 8 caracteres, com maiúscula, minúscula, número e caractere especial.</p></div>
      <div class="field"><label for="senha2">Confirmar senha</label>
        <div class="control"><?= icon('lock', 18) ?><input type="password" id="senha2" name="senha2" placeholder="Repita a senha criada" required></div></div>
      <p class="hint mb">O cadastro público cria contas de <b>Paciente</b>. Médicos e administradores são cadastrados pela clínica.</p>
      <button class="btn" type="submit">Cadastrar</button>
    </form>
<?php auth_end('Já tem conta? <a href="' . e(url('login.php')) . '">Entrar</a>');
