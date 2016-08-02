<div class="members profile">
    <h1>Hi <?= $this->member->getFirstname() ?> <?= $this->member->getLastname() ?>!</h1>

    <div class="profile-page"></div>

    <div>
        <a href="<?= \Members\Model\Configuration::getLocalizedPath('routes.logout') ?>" class="btn btn-default"><?=$this->translate('Logout')?></a>
    </div>

</div>