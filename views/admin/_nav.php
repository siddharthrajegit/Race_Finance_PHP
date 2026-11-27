<?php
$activeMenu = $activeMenu ?? 'admin-dashboard';
$adminTabs = [
    'admin-dashboard' => ['href' => '/admin',          'icon' => 'bi-speedometer2',        'label' => 'Dashboard'],
    'admin-users'     => ['href' => '/admin/users',    'icon' => 'bi-people',              'label' => 'Subscribers'],
    'admin-firms'     => ['href' => '/admin/firms',    'icon' => 'bi-building',            'label' => 'Firms'],
    'admin-invoices'  => ['href' => '/admin/invoices', 'icon' => 'bi-receipt',             'label' => 'Invoices'],
    'admin-system'    => ['href' => '/admin/system',   'icon' => 'bi-hdd-network',         'label' => 'System'],
    'admin-settings'  => ['href' => '/admin/settings', 'icon' => 'bi-sliders2',            'label' => 'Settings'],
];
?>
<div class="mb-6 pb-5 border-b border-border flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
  <div>
    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-canvas-alt border border-border text-[11px] font-medium tracking-wide uppercase text-ink-muted mb-2">
      <i class="bi bi-shield-lock text-[11px]"></i> Platform Console
    </div>
    <h1 class="text-2xl font-semibold tracking-tight text-ink mb-0.5">Platform Administration</h1>
    <p class="text-sm text-ink-muted mb-0">System overview, subscriber accounts, storage quotas, and global defaults.</p>
  </div>

  <nav class="flex flex-wrap items-center gap-1.5 bg-canvas-alt/80 p-1 rounded-xl border border-border" aria-label="Admin sections">
    <?php foreach ($adminTabs as $key => $tab):
      $isActive = ($activeMenu === $key);
    ?>
      <a href="<?= $tab['href'] ?>"
         class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium transition-colors whitespace-nowrap <?= $isActive ? 'bg-surface text-ink shadow-xs border border-border' : 'text-ink-muted hover:text-ink hover:bg-surface/60' ?>">
        <i class="bi <?= $tab['icon'] ?> text-[13px]"></i>
        <span><?= $tab['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</div>
