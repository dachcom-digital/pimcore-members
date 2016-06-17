<form action="<?= $this->loginUri ?>" method="post">
    <input type="hidden" name="back" value="<?=$this->back?>" />
    <?php if ( $this->areaMode ) { ?>
        <input type="hidden" name="lang" value="<?=$this->language?>" />
        <input type="hidden" name="origin" value="<?=$this->origin?>" />
    <?php } ?>

    <div class="form-group <?= $this->error ? 'has-error' : '' ?>">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
            <input type="email" class="form-control input-lg" name="email" placeholder="<?= $this->translate('Email') ?>">
        </div>
    </div>

    <div class="form-group <?= $this->error ? 'has-error' : '' ?>">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-fw fa-lock"></i></span>
            <input type="password" class="form-control input-lg" name="password" placeholder="<?= $this->translate('Password') ?>">
        </div>
    </div>

    <?php if ($this->error) { ?>
        <div class="form-group has-error">
            <span class="help-block"><?= $this->error ?></span>
        </div>
    <?php } ?>

    <button class="btn btn-lg btn-primary btn-block" type="submit">
        <?= $this->translate('Login') ?>
    </button>

    <div class="row">
        <div class="col-sm-6">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember">
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

</form>