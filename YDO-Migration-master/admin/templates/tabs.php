<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<br />
<div class="container">
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#home">Dev To Staging</a></li>
  <li><a data-toggle="tab" href="#menu1">Staging To Prod</a></li>
  <li><a data-toggle="tab" href="#menu2">Prod to Staging</a></li>
</ul>

<div class="tab-content">
  <div id="home" class="tab-pane fade in active">
    <?php require_once YDO_PLUGIN_DIR . '/admin/templates/dev_tostaging.php';?>
  </div>
  <div id="menu1" class="tab-pane fade">
     <?php require_once YDO_PLUGIN_DIR . '/admin/templates/staging_to_prod.php';?>
  </div>
  <div id="menu2" class="tab-pane fade">
     <?php require_once YDO_PLUGIN_DIR . '/admin/templates/prod_to_staging.php';?>
  </div>
</div>

</div>