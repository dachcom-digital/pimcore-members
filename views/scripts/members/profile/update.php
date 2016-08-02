<div class="members profile-update">

    <h2 class="text-center">
        <?= $this->translate('Update profile') ?>
    </h2>

    <?php if (!empty($this->flashMessages)){ ?>
        <?php foreach ($this->flashMessages as $message) { ?>
            <div class="alert alert-<?= $message['type'] ?>" role="alert">
                <?= $message['text'] ?>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if( $this->editmode) { ?>
        <div class="alert alert-info"><?= $this->translateAdmin('user profile not visible in backend'); ?></div>
    <?php } else {?>
        <?= $this->template('members/form/userForm.php', array('type' => 'update', 'formAction' => \Members\Model\Configuration::getLocalizedPath('routes.profile.update'))); ?>
    <?php } ?>

</div>