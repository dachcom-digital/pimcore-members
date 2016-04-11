<div class="member login">

    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

            <?php if (!empty($this->flashMessages)): ?>
                <?php foreach($this->flashMessages as $message): ?>
                    <div class="alert alert-<?= $message['type'] ?>" role="alert">
                        <?= $message['text'] ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2 class="text-center">
                <?= $this->translate('Login') ?>
                <?= $this->translate('or') ?>
                <a href="<?= \Members\Model\Configuration::getLocalizedPath('routes.register') ?>">
                    <?= $this->translate('Sign up') ?>
                </a>
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
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-fw fa-lock"></i></span>
                        <input type="password" class="form-control input-lg" name="password"
                            placeholder="<?= $this->translate('Password') ?>">
                    </div>
                </div>

                <?php if ($this->error): ?>
                    <div class="form-group has-error">
                        <span class="help-block"><?= $this->error ?></span>
                    </div>
                <?php endif; ?>

                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    <?= $this->translate('Login') ?>
                </button>
            </form>

            <div class="row">
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" value="remember-me">
                            <?= $this->translate('Remember me') ?>
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <p class="forgot-pwd">
                        <a href="<?= \Members\Model\Configuration::getLocalizedPath('routes.passwordRequest') ?>">
                            <?= $this->translate('Forgot your password?') ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>