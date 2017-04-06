<?php if ($this->editmode || !$this->isLoggedIn) { ?>

    <?= $this->template('members/auth/login-area.php'); ?>

<?php } elseif ($this->isLoggedIn) { ?>

    <?php if (!$this->hideWhenLoggedIn && $this->href('showSnippedWhenLoggedIn')->getElement()) { ?>

        <?php

        $placeholder = new \Pimcore\Placeholder();
        $snippetContent = $this->inc($this->href('showSnippedWhenLoggedIn')->getFullPath());
        $params = [
            'user'        => $this->membersUser,
            'redirectUri' => $this->back,
            'logoutUri'   => $this->logoutUri,
            'currentUri'  => $this->origin
        ];
        ?>
        <?= $placeholder->replacePlaceholders($snippetContent, $params); ?>

    <?php } elseif (!$this->hideWhenLoggedIn) { ?>

        <?= $this->template('members/auth/login-area-logged-in.php') ?>

    <?php } ?>

<?php } ?>