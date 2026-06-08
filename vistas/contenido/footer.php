<div class="float-right d-none d-sm-block">
  <b>Versión</b> <?= htmlspecialchars($config['system_version'] ?? 'N/D') ?>
</div>

<strong>
  Copyright &copy; <?= date('Y') ?>.
  <?= htmlspecialchars($config['system_name'] ?? 'Sistema') ?> es desarrollado por 
  <a href="https://thebigtable.com.ar" target="_blank">
    <?= htmlspecialchars($config['system_author'] ?? 'The Big Table') ?>
  </a>
</strong>