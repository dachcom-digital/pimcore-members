<div class="members register">
    <div class="row">

        <a name="members-register-form"></a>
        <div class="col-xs-12">

            <h2 class="text-center">
                <?= $this->translate('Sign up') ?>
                <?= $this->translate('or') ?>
                <a href="<?= \Members\Model\Configuration::getLocalizedPath('routes.login')?>">
                    <?= $this->translate('Login') ?>
                </a>
            </h2>

            <?= $this->template('form/userForm.php', array('type' => 'create', 'formAction' => \Members\Model\Configuration::getLocalizedPath('routes.register'))); ?>

        </div>

    </div>

</div>