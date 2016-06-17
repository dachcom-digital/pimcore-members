<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Redirect after successful login') ?></label>
        <?php
        echo $this->href('redirectAfterSuccess', [
            'types'   => ['document'],
        ]); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Hide when logged in') ?></label>
        <?= $this->checkbox('hideWhenLoggedIn'); ?>
    </div>
</div>

<?php if ( !$this->checkbox('hideWhenLoggedIn')->getData() ) { ?>
<div class="row">
    <div class="col-xs-12">
        <label><?= $this->translate('Show this snippet when logged in') ?></label>
        <?php
        echo $this->href('showSnippedWhenLoggedIn', [
            'types'   => ['document'],
            'subtypes'   => ['snippet'],
        ]); ?>
    </div>
</div>
<?php } ?>