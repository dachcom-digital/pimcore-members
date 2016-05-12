<div class="auth-success">
    Hi, <?= $this->user['firstName'] ?>
    <a href="<?= $this->logoutUrl ?>" class="btn btn-default"><?=$this->translate('Logout')?></a>
</div>