<?php
/* Lembretes 24h antes da consulta.
   Execução agendada (a cada hora) */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.');
}
require __DIR__ . '/includes/bootstrap.php';
echo gerar_lembretes() . " lembrete(s) gerado(s).\n";
