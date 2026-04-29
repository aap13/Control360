<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'CSF Digital' ?> — Gestão de TI</title>

<?php
$__appCssVersion = @file_exists(__DIR__ . '/../assets/css/app.css') ? @filemtime(__DIR__ . '/../assets/css/app.css') : time();
$__appJsVersion  = @file_exists(__DIR__ . '/../assets/js/app.js') ? @filemtime(__DIR__ . '/../assets/js/app.js') : time();
$__bootstrapCssVersion = @file_exists(__DIR__ . '/../assets/css/bootstrap.min.css') ? @filemtime(__DIR__ . '/../assets/css/bootstrap.min.css') : time();
$__bootstrapJsVersion  = @file_exists(__DIR__ . '/../assets/js/bootstrap.bundle.min.js') ? @filemtime(__DIR__ . '/../assets/js/bootstrap.bundle.min.js') : time();
?>

    <link rel="stylesheet" href="<?= asset_url('css/bootstrap.min.css') ?>?v=<?= $__bootstrapCssVersion ?>">
<link rel="stylesheet" href="<?= asset_url('css/app.css') ?>?v=<?= $__appCssVersion ?>">
<script src="<?= asset_url('js/bootstrap.bundle.min.js') ?>?v=<?= $__bootstrapJsVersion ?>"></script>
<script src="<?= asset_url('js/app.js') ?>?v=<?= $__appJsVersion ?>" defer></script>
<link rel="icon" type="image/svg+xml" href="<?= asset_url('img/favicon.svg') ?>">
</head>
<body>
<?php
if (!function_exists('icon')) {
function icon($name, $cls='') {
    $icons = [
        'menu'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
        'theme'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3c0 .34-.02.67-.02 1a7 7 0 0 0 9.81 6.44c.01.11.01.23.01.35Z"/></svg>',
        'computer'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M3 15h18"/><path d="M8 20h8"/><path d="M12 16v4"/></svg>',
        'phone'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
        'box'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'users'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'clock'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
        'edit'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
        'logout'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'bill'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        'plus'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>',
        'check'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
        'off'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
        'trash'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
        'user'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/></svg>',
        'filter'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h18"/><path d="M6 12h12"/><path d="M10 19h4"/></svg>',
        'printer'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>',
        'ticket'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 12h6"/><path d="M9 16h6"/><path d="M9 8h2"/></svg>',
        'spark'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M5 19l.8 2 .7-2 2-.8-2-.7L5 15l-.7 2.3-2 .7 2 .8z"/></svg>',
        'swap'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M4 20 21 3"/><path d="M21 16v5h-5"/><path d="M15 15 3 3"/></svg>'
    ];
    $svg = $icons[$name] ?? $icons['box'];
    if ($cls) $svg = str_replace('<svg', '<svg class="'.$cls.'"', $svg);
    return $svg;
}}
$page  = basename($_SERVER['PHP_SELF']);
$tipog = $_GET['tipo'] ?? '';
$activeComp = $page==='computadores.php' || ($page==='cadastrar.php'&&$tipog==='computador') || ($page==='editar.php'&&$tipog==='computador');
$activeCel  = $page==='celulares.php'    || ($page==='cadastrar.php'&&$tipog==='celular')    || ($page==='editar.php'&&$tipog==='celular');
$activeAtivoHist = $page==='ativos_historico.php';
$roleLabel = ($_SESSION['perfil'] ?? '') === 'admin' ? 'Administrador' : 'Viewer';
$topbarSub = $page==='index.php' ? 'Visão geral do ambiente' : 'Gestão centralizada de ativos';
?>
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <?php if (file_exists(__DIR__.'/../logo.png')): ?>
      <img src="<?= app_url('logo.png') ?>" alt="CSF Digital">
    <?php else: ?>
      <div class="logo-fallback">
        <div class="logo-mark"><?= icon('computer') ?></div>
        <div class="logo-text"><strong>CSF Digital</strong><span>Gestão de ativos</span></div>
      </div>
    <?php endif; ?>
  </div>
  <div class="sidebar-scroll">
    <?php $secGeralOpen = $page==='index.php'; ?>
    <?php if (canAccess('dashboard')): ?>
    <div class="nav-section nav-drop <?= $secGeralOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Geral</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <a href="index.php" class="nav-link <?= $page==='index.php'?'active':'' ?>">
            <span class="ni"><?= icon('dashboard') ?></span>
            <span class="nav-meta"><strong>Dashboard</strong><span>Indicadores e resumo</span></span>
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $secInvOpen = $activeComp || $activeCel || $activeAtivoHist; ?>
    <?php if (canAccess('computadores') || canAccess('celulares')): ?>
    <div class="nav-section nav-drop <?= $secInvOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Inventário</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <?php if (canAccess('computadores')): ?>
          <a href="computadores.php" class="nav-link <?= $activeComp?'active':'' ?>">
            <span class="ni"><?= icon('computer') ?></span>
            <span class="nav-meta"><strong>Computadores</strong><span>Desktop e notebook</span></span>
          </a>
          <?php endif; ?>
          <?php if (canAccess('celulares')): ?>
          <a href="celulares.php" class="nav-link <?= $activeCel?'active':'' ?>">
            <span class="ni"><?= icon('phone') ?></span>
            <span class="nav-meta"><strong>Celulares</strong><span>Linha móvel e MDM</span></span>
          </a>
          <?php endif; ?>
          <?php if (canAccess('computadores') || canAccess('celulares')): ?>
          <a href="ativos_historico.php" class="nav-link <?= $activeAtivoHist?'active':'' ?>">
            <span class="ni"><?= icon('clock') ?></span>
            <span class="nav-meta"><strong>Histórico de ativos</strong><span>Timeline e reincidência</span></span>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $secFinOpen = in_array($page,['faturas.php','fornecedores.php'], true); ?>
    <?php if (canAccess('faturas') || canAccess('fornecedores')): ?>
    <div class="nav-section nav-drop <?= $secFinOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Financeiro</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <?php if (canAccess('faturas')): ?>
          <a href="faturas.php" class="nav-link <?= $page==='faturas.php'?'active':'' ?>">
            <span class="ni"><?= icon('bill') ?></span>
            <span class="nav-meta"><strong>Faturas</strong><span>Controle mensal</span></span>
          </a>
          <?php endif; ?>
          <?php if (canAccess('fornecedores')): ?>
          <a href="fornecedores.php" class="nav-link <?= $page==='fornecedores.php'?'active':'' ?>">
            <span class="ni"><?= icon('box') ?></span>
            <span class="nav-meta"><strong>Fornecedores</strong><span>Base de parceiros</span></span>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $secImpOpen = in_array($page,['distribuicao_index.php','distribuicao_cadastrar.php','distribuicao_editar.php','distribuicao_troca_tecnica.php','distribuicao_movimentacoes.php','distribuicao_importar_monitoramento.php','distribuicao_importar_base.php','impressao_financeiro.php','impressao_financeiro_importar.php','distribuicao_clientes.php'], true); ?>
    <?php if (canAccess('distribuicao') || canAccess('impressao_financeiro')): ?>
    <div class="nav-section nav-drop <?= $secImpOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Impressão</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <?php if (canAccess('distribuicao')): ?>
          <a href="distribuicao_index.php" class="nav-link <?= in_array($page,['distribuicao_index.php','distribuicao_cadastrar.php','distribuicao_editar.php','distribuicao_troca_tecnica.php','distribuicao_movimentacoes.php','distribuicao_importar_monitoramento.php','distribuicao_importar_base.php'], true)?'active':'' ?>">
            <span class="ni"><?= icon('printer') ?></span>
            <span class="nav-meta"><strong>Distribuição</strong><span>Clientes, parque e trocas</span></span>
          </a>
          <?php endif; ?>
          <?php if (canAccess('impressao_financeiro') || canAccess('distribuicao') || canAccess('faturas')): ?>
          <a href="impressao_financeiro.php" class="nav-link <?= in_array($page,['impressao_financeiro.php','impressao_financeiro_importar.php'], true)?'active':'' ?>">
            <span class="ni"><?= icon('bill') ?></span>
            <span class="nav-meta"><strong>Financeiro impressão</strong><span>Páginas, aluguel e total</span></span>
          </a>
          <?php endif; ?>
          <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
          <a href="distribuicao_clientes.php" class="nav-link <?= $page==='distribuicao_clientes.php'?'active':'' ?>">
            <span class="ni"><?= icon('users') ?></span>
            <span class="nav-meta"><strong>Clientes distribuição</strong><span>Acesso por cliente</span></span>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $secAtOpen = in_array($page,['chamados_index.php','chamados_visualizar.php','chamados_responder.php','chamados_atribuir.php','chamados_status.php','chamados_relatorios.php','chamados_categorias.php'], true); ?>
    <?php if (canAccess('chamados')): ?>
    <div class="nav-section nav-drop <?= $secAtOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Atendimento</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <a href="chamados_index.php" class="nav-link <?= in_array($page,['chamados_index.php','chamados_visualizar.php','chamados_responder.php','chamados_atribuir.php','chamados_status.php'], true)?'active':'' ?>">
            <span class="ni"><?= icon('ticket') ?></span>
            <span class="nav-meta"><strong>Chamados</strong><span>Fila, resposta e triagem</span></span>
          </a>
          <a href="chamados_relatorios.php" class="nav-link <?= $page==='chamados_relatorios.php'?'active':'' ?>">
            <span class="ni"><?= icon('dashboard') ?></span>
            <span class="nav-meta"><strong>Relatórios</strong><span>Métricas do portal público</span></span>
          </a>
          <a href="chamados_categorias.php" class="nav-link <?= $page==='chamados_categorias.php'?'active':'' ?>">
            <span class="ni"><?= icon('box') ?></span>
            <span class="nav-meta"><strong>Categorias e SLA</strong><span>Definições de atendimento</span></span>
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php $secAdmOpen = $page==='usuarios.php'; ?>
    <?php if (canAccess('usuarios')): ?>
    <div class="nav-section nav-drop <?= $secAdmOpen ? 'open' : '' ?>">
      <button type="button" class="nav-drop-toggle" onclick="toggleNavDrop(this)">
        <span class="nav-label">Administração</span><span class="nav-drop-caret">▾</span>
      </button>
      <div class="nav-drop-panel">
        <div class="nav-stack">
          <a href="usuarios.php" class="nav-link <?= $page==='usuarios.php'?'active':'' ?>">
            <span class="ni"><?= icon('users') ?></span>
            <span class="nav-meta"><strong>Usuários</strong><span>Permissões e acesso</span></span>
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <div class="sidebar-footer">
    <?php if (!is_viewer()): ?><a href="perfil.php" class="sidebar-user-card"><?php else: ?><div class="sidebar-user-card"><?php endif; ?>
      <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['nome']??'?',0,1)) ?></div>
      <div style="min-width:0">
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></div>
        <div class="sidebar-user-role"><?= $roleLabel ?></div>
      </div>
    <?php if (!is_viewer()): ?></a><?php else: ?></div><?php endif; ?>
    <a href="logout.php" class="sidebar-logout-link"><span class="ni"><?= icon('logout') ?></span><span>Sair do sistema</span></a>
  </div>
</aside>
<div class="main">
  <div class="topbar">
    <div class="topbar-inner">
      <div style="display:flex;align-items:center;gap:12px;min-width:0">
        <button class="menu-toggle" onclick="openSidebar()"><?= icon('menu') ?></button>
        <div class="topbar-title">
          <span class="topbar-title-icon"><?php
              if (isset($pageIconSvg)) echo $pageIconSvg;
              elseif ($page === 'index.php') echo icon('dashboard');
              elseif ($page === 'computadores.php') echo icon('computer');
              elseif ($page === 'celulares.php') echo icon('phone');
              elseif ($page === 'faturas.php' || $page === 'impressao_financeiro.php' || $page === 'impressao_financeiro_importar.php') echo icon('bill');
              elseif ($page === 'fornecedores.php') echo icon('box');
              elseif (in_array($page, ['chamados_index.php','chamados_visualizar.php','chamados_responder.php','chamados_atribuir.php','chamados_status.php','chamados_relatorios.php'], true)) echo icon('ticket');
              elseif ($page === 'usuarios.php') echo icon('users');
              else echo icon(($tipog ?? '') === 'celular' ? 'phone' : 'computer');
          ?></span>
          <div class="topbar-title-text">
            <strong><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></strong>
            <span><?= $topbarSub ?></span>
          </div>
        </div>
      </div>
      <div class="topbar-actions">
        <button type="button" class="theme-toggle theme-toggle-subtle" onclick="toggleTheme()"><?= icon('theme') ?><span data-theme-label>Tema escuro</span></button>
      </div>
    </div>
  </div>
  <div class="content-wrap"><div class="content">
<script>
function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('overlay').classList.add('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}
function toggleNavDrop(button){var section=button.closest('.nav-drop'); if(section){section.classList.toggle('open');}}
</script>
