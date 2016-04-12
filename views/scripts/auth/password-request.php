<div class="member remind">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

            <?php if (!empty($this->flashMessages)): ?>
                <?php foreach ($this->flashMessages as $message): ?>
                    <div class="alert alert-<?= $message['type'] ?>" role="alert">
                        <?= $message['text'] ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2 class="text-center">
                <?= $this->translate('Reset your password') ?><br>
                <small><?= $this->translate('Get a new password by filling out the form below.') ?></small>
            </h2>

            <form action="<?= $this->request->getRequestUri() ?>" method="post">
                <div class="form-group <?= $this->error ? 'has-error' : '' ?>">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
                        <input type="email" class="form-control input-lg" name="email"
                            placeholder="<?= $this->translate('Email') ?>">
                    </div>
                </div>
                <div class="form-group <?= $this->error ? 'has-error' : '' ?>">
                    <span class="help-block"><?= $this->error ?></span>
                </div>

                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    <?= $this->translate('Reset password') ?>
                </button>
            </form>
        </div>
    </div>
</div>