<div class="members login">

    <div class="row">

        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

            <?php if (!empty($this->flashMessages)){ ?>
                <?php foreach ($this->flashMessages as $message) { ?>
                    <div class="alert alert-<?= $message['type'] ?>" role="alert">
                        <?= $message['text'] ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <h2 class="text-center">
                <?= $this->translate('Login') ?>
                <?= $this->translate('or') ?>
                <a href="<?= \Members\Model\Configuration::getLocalizedPath('routes.register') ?>">
                    <?= $this->translate('Sign up') ?>
                </a>
            </h2>

            <?= $this->template('form/loginForm.php', array('loginUri' => $this->request->getRequestUri(), 'error' => $this->error, 'back' => $this->back)); ?>

        </div>

    </div>

</div>