<div class="toolbox-edit-overlay">

    <div class="t-row">
        <label><?= $this->translateAdmin('Redirect after successful login') ?></label>
        <?= $this->href('redirectAfterSuccess', [
            'types'    => ['document'],
            'subtypes' => [
                'document' => ['page', 'link', 'hardlink']
            ]
        ]); ?>

    </div>

    <div class="t-row">
        <label><?= $this->translateAdmin('Hide when logged in') ?></label>
        <?= $this->checkbox('hideWhenLoggedIn'); ?>
    </div>

    <?php if (!$this->checkbox('hideWhenLoggedIn')->getData()) { ?>
        <div class="t-row">
            <label><?= $this->translateAdmin('Show this snippet when logged in') ?></label>
            <?= $this->href('showSnippedWhenLoggedIn', [
                'types'    => ['document'],
                'subtypes' => [
                    'document' => ['snippet']
                ]
            ]); ?>
        </div>
    <?php } ?>

</div>