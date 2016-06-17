<div class="members login">
    <?= $this->template('form/loginForm.php', [
        'language' => $this->language,
        'areaMode' => TRUE,
        'loginUri' => $this->loginUri,
        'back' => $this->back,
        'origin' => $this->request->getRequestUri(),
        'error' => $this->error
    ]); ?>
</div>