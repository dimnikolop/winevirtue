<?php use app\src\Application; ?>
<?php if (Application::$app->session->getFlash('success')): ?>
      <div class="alert alert-success" role="alert">
      	<p><i class="fa fa-check-circle"></i> <strong>Success!</strong>
          <?php
          	echo Application::$app->session->getFlash('success');
          ?>
      	</p>
      </div>
<?php endif ?>

<?php if (Application::$app->session->getFlash('error')): ?>
      <div class="alert alert-danger" role="alert">
      	<p><i class="fa fa-times-circle"></i> <strong>Error!</strong>
          <?php
          	echo Application::$app->session->getFlash('error');
          ?>
      	</p>
      </div>
<?php endif ?>