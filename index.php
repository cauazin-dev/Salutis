<?php
/* Tela de abertura (splash) e redirecionamento conforme o perfil */
require __DIR__ . '/includes/bootstrap.php';

if ($u = usuario()) {
    redirect(dashboard_url($u['perfil']));
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="2;url=<?= e(url('login.php')) ?>">
<title>Salutis · Seu portal de saúde</title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="auth splash">
  <div class="auth-logo">
    <?= icon('heart', 84) ?>
    <h1>SALUTIS</h1>
    <p>Seu portal de saúde</p>
    <div class="spinner"></div>
  </div>
  <p class="auth-foot"><a href="<?= url('login.php') ?>">Entrar agora</a></p>
</body>
</html>
