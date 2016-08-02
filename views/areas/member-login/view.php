<?php if ($this->editmode || !$this->isLoggedIn) { ?>

    <?= $this->template('members/auth/login-area.php', [
        'language' => $this->language,
        'areaMode' => TRUE,
        'loginUri' => $this->loginUri,
        'back' => $this->back,
        'origin' => $this->request->getRequestUri(),
        'error' => $this->error
    ]); ?>


<?php } elseif ($this->isLoggedIn) { ?>

    <?php if ( !$this->hideWhenLoggedIn && $this->href('showSnippedWhenLoggedIn')->getElement() ) { ?>

        <?=$this->inc($this->href('showSnippedWhenLoggedIn')->getFullPath()); ?>

    <?php } elseif ( !$this->hideWhenLoggedIn ) { ?>

        <?= $this->template('members/auth/login-area-logged-in.php') ?>

    <?php } ?>

<?php } ?>