<div class="members register">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

            <form action="<?= $this->formAction ?>" method="post">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="form-group <?= isset($this->errors['firstname']) ? 'has-error' : '' ?>">
                            <input type="text" name="firstname" id="firstname"
                                class="form-control input-lg" tabindex="1"
                                placeholder="<?= $this->translate('First name') ?>"
                                value="<?= $this->type == 'update' ? $this->member->getFirstname() : $this->firstname ?>">
                            <div class="help-block">
                                <?= @reset($this->errors['firstname']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="form-group <?= isset($this->errors['lastname']) ? 'has-error' : '' ?>">
                            <input type="text" name="lastname" id="lastname"
                                class="form-control input-lg" tabindex="2"
                                placeholder="<?= $this->translate('Last Name') ?>"
                                value="<?= $this->type == 'update' ? $this->member->getLastName() : $this->lastname ?>">
                            <div class="help-block">
                                <?= @reset($this->errors['lastname']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group <?= isset($this->errors['email']) ? 'has-error' : '' ?>">
                    <input type="email" name="email" id="email"
                        class="form-control input-lg" tabindex="3"
                        placeholder="<?= $this->translate('Email address') ?>"
                        value="<?= $this->type == 'update' ? $this->member->getEmail() : $this->email ?>"
                        <?= $this->type == 'update' ? 'readonly="readonly"' : '' ?>>
                    <div class="help-block">
                        <?= @reset($this->errors['email']) ?>
                    </div>
                </div>

                <?php if( $this->type == 'create' ) {?>

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

                    <div class="row">
                        <div class="col-xs-4 col-sm-3">
                            <div class="form-group <?= isset($this->errors['agree']) ? 'has-error' : '' ?>">
                                <label class="btn btn-default <?= isset($this->errors['agree']) ? 'btn-danger' : '' ?>" tabindex="6">
                                    <input type="checkbox" name="agree" id="agree" value="1"
                                        <?= $this->agree ? 'checked' : '' ?>>
                                    <?= $this->translate('I agree') ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-8 col-sm-9">
                            <div class="form-group <?= isset($this->errors['firstname']) ? 'has-error' : '' ?>">

                                <?= sprintf(
                                    $this->translate('By clicking %s, you agree to %s'),
                                    '<strong class="label label-primary">' .$this->translate('Register') . '</strong>',
                                    '<a href="#" data-toggle="modal" data-target="#terms">"' . $this->translate('Terms and Conditions') . '"</a>'
                                ) ?>.

                            </div>
                        </div>
                    </div>

                <?php } ?>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <?= $this->translate($this->type == 'create' ? 'Create account' : 'Update account') ?>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php if( $this->type == 'create' ) { ?>

        <div class="<?= $this->editmode ? '' : 'modal fade' ?>" id="terms" tabindex="-1" role="dialog"
            aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×
                        </button>
                        <h4 class="modal-title" id="termsModalLabel">
                            <?= $this->input('terms_title') ?>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <?= $this->wysiwyg('terms') ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            <?= $this->translate('I agree') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>

</div>