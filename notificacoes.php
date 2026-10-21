<?php
/* US6 — Central de notificações (lembretes 24h, remarcações, avisos) */
require __DIR__ . '/includes/bootstrap.php';
$u = exigir_login();

$lista = db_all('SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY criada_em DESC, id DESC LIMIT 100', [$u['id']]);

page_start('Notificações', ['back' => dashboard_url($u['perfil']), 'h1' => 'Notificações', 'sub' => 'Lembretes de consulta e avisos importantes']);
?>
<div class="card">
<?php if (!$lista): ?>
  <p class="muted">Você não tem notificações.</p>
<?php else: foreach ($lista as $n): ?>
  <div class="item">
    <div class="avatar" style="background:<?= $n['lida'] ? '#94a3b8' : 'var(--sky-d)' ?>"><?= icon('bell', 20) ?></div>
    <div class="body">
      <b><?= e($n['titulo']) ?> <?= $n['lida'] ? '' : badge('Nova', 'blue') ?></b>
      <span><?= e($n['mensagem']) ?></span>
      <span class="meta"><?= e(dt_br($n['criada_em'])) ?></span>
    </div>
    <?php if ($n['consulta_id'] && $u['perfil'] === 'paciente'): ?>
      <a class="btn btn-ghost btn-sm" href="<?= url('paciente/consulta_detalhe.php?id=' . (int) $n['consulta_id']) ?>">Ver</a>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>
</div>
<?php
db_exec('UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? AND lida = 0', [$u['id']]);
page_end();
