<div class="members profile">

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

    <?= $this->template('form/userForm.php', array('type' => 'update', 'formAction' => \Members\Model\Configuration::getLocalizedPath('routes.profile.update'))); ?>

</div>