<?php
require __DIR__ . '/includes/bootstrap.php';
encerrar_sessao();
iniciar_sessao_http();
flash('Você saiu do sistema.', 'info');
redirect('login.php');
