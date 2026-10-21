<?php
/* Download protegido de laudos e anexos do chat (verifica permissão antes de servir) */
require __DIR__ . '/includes/bootstrap.php';
$u = exigir_login(['paciente', 'medico']);

$tipo = $_GET['t'] ?? '';
$id   = (int) ($_GET['id'] ?? 0);
$rel  = null;
$orig = 'arquivo';

if ($tipo === 'exame') {
    $x = db_one('SELECT * FROM exames WHERE id = ? AND status = \'disponivel\' AND arquivo IS NOT NULL', [$id]);
    if ($x) {
        $ok = $u['perfil'] === 'paciente'
            ? (int) $x['paciente_id'] === paciente_id()
            : medico_atende_paciente(medico_id(), (int) $x['paciente_id']);
        if ($ok) { $rel = $x['arquivo']; $orig = $x['arquivo_original'] ?: 'laudo.pdf'; }
    }
} elseif ($tipo === 'chat') {
    $m = db_one('SELECT * FROM telemed_mensagens WHERE id = ? AND arquivo IS NOT NULL', [$id]);
    if ($m && consulta_acessivel((int) $m['consulta_id'])) { $rel = $m['arquivo']; $orig = $m['arquivo_original'] ?: 'anexo'; }
}

$base = realpath(APP_ROOT . '/storage/uploads');
$path = $rel ? realpath($base . '/' . $rel) : false;
if (!$path || strpos($path, $base) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('Arquivo não encontrado ou sem permissão de acesso.');
}

$tipos = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
$ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));
header('Content-Type: ' . ($tipos[$ext] ?? 'application/octet-stream'));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . (isset($_GET['dl']) ? 'attachment' : 'inline') . '; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
