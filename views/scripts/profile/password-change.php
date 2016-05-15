<div class="members remind">
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
                <?= $this->translate('Change password') ?>
            </h2>

            <form action="<?= $this->request->getRequestUri() ?>" method="post">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group <?= isset($this->errors['password_current']) ? 'has-error' : '' ?>">
                            <input type="password" name="password_current" id="password_current"
                                class="form-control input-lg" tabindex="4"
                                placeholder="<?= $this->translate('current password') ?>">

                            <div class="help-block">
                                <?= @reset($this->errors['password_current']) ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?= isset($this->errors['password']) ? 'has-error' : '' ?>">
                            <input type="password" name="password" id="password"
                                class="form-control input-lg" tabindex="4"
                                placeholder="<?= $this->translate('Password') ?>">

                            <div class="help-block">
                                <?= @reset($this->errors['password']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group <?= isset($this->errors['password_confirm']) ? 'has-error' : '' ?>">
                            <input type="password" name="password_confirm" id="password_confirm"
                                class="form-control input-lg" tabindex="5"
                                placeholder="<?= $this->translate('Confirm password') ?>">

                            <div class="help-block">
                                <?= @reset($this->errors['password_confirm']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    <?= $this->translate('Change password') ?>
                </button>
            </form>
        </div>
    </div>
</div>