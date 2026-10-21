# SALUTIS — Plataforma Digital de Agendamento, Telemedicina e Gestão Clínica

PHP 8 (PDO) + MySQL/MariaDB + HTML/CSS, sem frameworks. Identidade visual extraída do protótipo (azul-marinho, azul-céu, cartões brancos, tiles coloridos).

## Instalação no XAMPP

1. Copie a pasta `salutis` para `C:\xampp\htdocs\`.
2. Inicie **Apache** e **MySQL** no painel do XAMPP.
3. Abra `http://localhost/phpmyadmin` → aba **Importar** → selecione `database/salutis.sql` → **Executar**
   (cria o banco `salutis`, as tabelas e os dados de demonstração).
4. Acesse **http://localhost/salutis/**

Conexão (`config/config.php`): host `127.0.0.1`, usuário `root`, senha vazia (padrão do XAMPP). Ajuste se necessário.
Requer PHP 8.0+ (extensões `pdo_mysql`, `mbstring`, `fileinfo` — já ativas no XAMPP).

## Contas de demonstração

| Perfil   | E-mail                  | Senha         |
|----------|-------------------------|---------------|
| Paciente | felipe@salutis.com      | Paciente@123  |
| Médico   | erick@salutis.com       | Medico@123    |
| Admin    | admin@salutis.com       | Admin@123     |

Outros médicos: `marcileide@`, `joao@`, `lucas@`, `fernanda@`, `rafael@salutis.com` (mesma senha de médico).

## Estrutura

```
config/       configurações (banco, modo demo, regras de tempo)
database/     salutis.sql (esquema + dados de exemplo)
includes/     bootstrap, db (PDO), auth/CSRF, regras de negócio (dominio.php), layout, funções
paciente/     telas do paciente (dashboard, agendar, consultas, fila, triagem, histórico, receitas, exames, pagamento, unidades, perfil)
medico/       cockpit, atendimento (prontuário/receitas/exames), agenda, histórico do paciente, laudos
admin/        painel, usuários, médicos, agendas/horários, especialidades, unidades, relatórios (CSV)
telemedicina/ sala de vídeo (Jitsi/WebRTC) + chat com anexos
documentos/   receita e registro de atendimento para impressão/PDF
storage/      uploads e log de e-mails (bloqueado por .htaccess)
```

## Regras de negócio implementadas (user stories do PDF)

- **US1 Autenticação** — e-mail e CPF válidos, senha forte (8+, maiúscula, minúscula, número, especial), recuperação por link com token de 1 h, bloqueio após 5 falhas em 10 min, redirecionamento ao painel do perfil.
- **US2 Agendamento** — filtro por especialidade/busca, calendário com horários livres gerados da grade do médico, antecedência mínima, janela máxima de 60 dias, bloqueio de horário duplicado (índice único no banco) e de dois agendamentos do paciente no mesmo horário, confirmação em tela e por e-mail.
- **US3 Consultas futuras** — lista com status (Confirmada, Na fila, Em atendimento, Concluída, Cancelada) e detalhes.
- **US4 Cancelar/Remarcar** — motivo obrigatório; só consultas confirmadas e futuras; horário liberado na hora; aviso ao médico e ao paciente.
- **US5 Histórico** — ordem decrescente, detalhe e download; acesso restrito (paciente só vê o seu; médico só de pacientes que atendeu).
- **US6 Notificações** — lembrete 24 h antes, alerta de remarcação/cancelamento, central de avisos com contador.
- **US7 Admin** — CRUD de usuários, médicos, especialidades, unidades e horários (grade semanal); relatórios por período com exportação CSV.
- **US8 Médico** — fila do dia priorizada pela triagem, prontuário, diagnóstico, receitas digitais, solicitação de exames, laudos, registro de faltas.
- **US9 Telemedicina** — fila virtual (check-in só 15 min antes e com triagem preenchida), um atendimento por vez, sala de vídeo e chat com envio de PDF/JPG/PNG (5 MB).
- **US10 Triagem** — sintomas, escala de dor 0–10, resumo sintético para o médico; dor ≥ 8 ou falta de ar = prioridade alta.
- **Backlog fase 3/4** — receitas (ativa/vencida), resultados de exames, pagamento simulado, unidades com mapa/rota.

## Observações

- **Modelo de dados**: o ER do PDF (PACIENTE, MARCAR_CONSULTA, TIPO_ESPECIALIDADE_CONSULTA) foi mantido como núcleo (`pacientes`, `consultas`, `especialidades`/`medicos`) e estendido com as tabelas exigidas pelas demais funcionalidades.
- **Cadastro público** cria somente pacientes. O protótipo oferece "Tipo de Perfil" com Médico/Admin no cadastro; por segurança, médicos e administradores são criados pelo painel admin.
- **`MODO_DEMO`** (`config/config.php`): mostra as contas de teste e o link de recuperação de senha na tela, e libera fila/atendimento fora do horário. **Desligue em produção.**
- **E-mails** são gravados em `storage/emails.log` (XAMPP não tem SMTP); para enviar de verdade configure `sendmail` e ative `MAIL_ENABLED`.
- **Pagamento** é simulado (sem gateway real). **Vídeo** usa Jitsi (`meet.jit.si`), exige internet.
- Lembretes também podem rodar por agendador: `php cron_lembretes.php`.
