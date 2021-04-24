<?php
$data = array(
    'title' => $title,
    'body_class' => 'bs-docs-home',
);
View::tplInclude('Public/header', $data); ?>
<main class="bs-docs-masthead" id="content" role="main">
  <div class="container">
  <h1><?php echo $title;?></h1>
      <p class="lead">本项目遵循MIT协议。</p>
    <p>
        <a href="https://github.com/changingsky502/appdownload" target='_blank' class="btn btn-outline-inverse btn-lg">Fork
            On Github</a>
    </p>
  </div>
</main>
<?php View::tplInclude('Public/footer'); ?>
