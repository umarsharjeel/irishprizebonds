<head>

  <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css" />
  <link href="https://cdn.materialdesignicons.com/1.7.22/css/materialdesignicons.min.css" rel="stylesheet">

  <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />


  <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />


   <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js" type="text/javascript"></script>

<style>
   <?php

    if(antelope_config()["antelope_custom_theme"]):
      $primary = antelope_config()["antelope_theme_color_primary"];
      $primary_text = antelope_config()["antelope_theme_color_primary_text"];
      $secondary = antelope_config()["antelope_theme_color_secondary"];
      $secondary_text = antelope_config()["antelope_theme_color_secondary_text"];
      ?>

      .logo{
        color: <?php echo $primary ?> !important;
      }

      .btn-primary {
        background-color: <?php echo $primary ?>  !important;
        border: 1px solid <?php echo $primary ?>  !important;
      }

      .btn-primary:hover {
        opacity: 0.9 !important;
        background-color: <?php echo $primary ?>  !important;
        border: 1px solid <?php echo $primary ?>  !important;
      }

      .panel-primary{
        border: 3px solid <?php echo $primary ?> !important;
      }

   <?php endif; ?>
   </style>


</head>
<body>
   <div class="wrapper-page">
      <div class="panel panel-color panel-primary panel-pages">
         <div class="panel-body">
            <h3 class="text-center m-t-0 m-b-15"> <a href="#" class="logo logo-admin"><?php echo $this->config->item("antelope_config")["antelope_brand_name"] ?></a></h3>
            <h4 class="text-muted text-center m-t-0"><b>Sign In</b></h4>
            <form method="post" class="form-horizontal m-t-20" action="<?php echo base_url() ?>user_management/login/">
               <div class="form-group">
                  <div class="col-xs-12"> <input name="username" class="form-control" type="text" required="" placeholder="Username"></div>
               </div>
               <div class="form-group">
                  <div class="col-xs-12"> <input name="password" class="form-control" type="password" required="" placeholder="Password"></div>
               </div>
               <!-- <div class="form-group">
                  <div class="col-xs-12">
                     <div class="checkbox checkbox-primary"> <input id="checkbox-signup" type="checkbox"> <label for="checkbox-signup"> Remember me </label></div>
                  </div>
               </div> -->
               <?php if(isset($error)): ?>
               <div class="alert alert-danger">
                <strong>Wrong Credentials</strong> please try again.
              </div>
            <?php endif; ?>

               <div class="form-group text-center m-t-40">
                  <div class="col-xs-12"> <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Log In</button></div>
               </div>
               <!-- <div class="form-group m-t-30 m-b-0">
                  <div class="col-sm-7"> <a href="pages-recoverpw.html" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your password?</a></div>
                  <div class="col-sm-5 text-right"> <a href="pages-register.html" class="text-muted">Create an account</a></div>
               </div> -->
            </form>
         </div>
      </div>
   </div>

   <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/detect.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/fastclick.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/waves.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/wow.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.nicescroll.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.scrollTo.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/app.js" type="text/javascript"></script>

</body>
