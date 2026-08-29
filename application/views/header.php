<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->

    <head>
        <meta charset="utf-8" />
        <title><?php echo $pageTitle ?> | Dashboard</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="content-type" content="text/html;charset=UTF-8">
        <meta name="robots" content="noindex">


        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css" />
        <link href="https://cdn.materialdesignicons.com/1.7.22/css/materialdesignicons.min.css" rel="stylesheet">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />


        <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />


         <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>

         <script src="https://code.highcharts.com/highcharts.js"></script>
         <script src="https://code.highcharts.com/modules/data.js"></script>
         <script src="https://code.highcharts.com/modules/exporting.js"></script>


<style>

.permissions_list{
  min-height:200px;
}

<?php

 if(antelope_config()["antelope_custom_theme"]):
   $primary = antelope_config()["antelope_theme_color_primary"];
   $primary_text = antelope_config()["antelope_theme_color_primary_text"];
   $secondary = antelope_config()["antelope_theme_color_secondary"];
   $secondary_text = antelope_config()["antelope_theme_color_secondary_text"];
   ?>

   .logo,.logo span, .logo-sm span{
     color: <?php echo $primary ?> !important;
   }

   #sidebar-menu  ul  li  a.active {
       color: <?php echo $primary ?> !important;
   }

   #sidebar-menu  ul  li  a.active i {
    color: <?php echo $primary ?> !important;
  }
  .button-menu-mobile {
    background: <?php echo $secondary_text ?> !important;
    color: <?php echo $secondary ?> !important;
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

  .page-header-title,.navbar-default {
    background-color: <?php echo $secondary ?>  !important;
  }

  .nav > li > a, .nav > li > a > span, .page-title {
    color: <?php echo $secondary_text ?> !important;

  }




<?php endif; ?>
</style>


    </head>

<body class="fixed-left">


  <div id="wrapper">
    <div class="topbar">
      <div class="topbar-left">
          <div class="text-center"> <a href="<?php echo base_url() ?>overview" class="logo"><?php echo $this->config->item("antelope_config")["antelope_brand_name"] ?></a> <a href="<?php echo base_url() ?>/dashboard/overview" class="logo-sm"><span><?php echo substr(antelope_config()["antelope_brand_name"],0,1) ?></span></a>
          </div>
      </div>
      <div class="navbar navbar-default" role="navigation">
          <div class="container">
              <div class="">
                  <div class="pull-left">
                      <button type="button" class="button-menu-mobile open-left waves-effect waves-lightx"> <i class="mdi mdi-menu"></i> </button> <span class="clearfix"></span>
                  </div>
                  <!-- <form class="navbar-form pull-left" role="search">
                      <div class="form-group">
                          <input type="text" class="form-control search-bar" placeholder="Search...">
                      </div>
                      <button type="submit" class="btn btn-search"><i class="fa fa-search"></i>
                      </button>
                  </form> -->
                  <ul class="nav navbar-nav navbar-right pull-right">
                      <li class="dropdown hidden-xs">
                          <!-- <a href="#" data-target="#" class="dropdown-toggle waves-effect waves-lightx notification-icon-box" data-toggle="dropdown" aria-expanded="true"> <i class="fa fa-bell"></i> <span class="badge badge-xs badge-danger"></span> </a> -->
                          <ul class="dropdown-menu dropdown-menu-lg">
                              <li class="text-center notifi-title">Notification <span class="badge badge-xs badge-success">3</span>
                              </li>
                              <li class="list-group">
                                  <a href="javascript:void(0);" class="list-group-item">
                                      <div class="media">
                                          <div class="media-heading">Your order is placed</div>
                                          <p class="m-0"> <small>Dummy text of the printing and typesetting industry.</small>
                                          </p>
                                      </div>
                                  </a>
                                  <a href="javascript:void(0);" class="list-group-item">
                                      <div class="media">
                                          <div class="media-body clearfix">
                                              <div class="media-heading">New Message received</div>
                                              <p class="m-0"> <small>You have 87 unread messages</small>
                                              </p>
                                          </div>
                                      </div>
                                  </a>
                                  <a href="javascript:void(0);" class="list-group-item">
                                      <div class="media">
                                          <div class="media-body clearfix">
                                              <div class="media-heading">Your item is shipped.</div>
                                              <p class="m-0"> <small>It is a long established fact that a reader will</small>
                                              </p>
                                          </div>
                                      </div>
                                  </a>
                                  <a href="javascript:void(0);" class="list-group-item"> <small class="text-primary">See all notifications</small> </a>
                              </li>
                          </ul>
                      </li>

                      <li class="dropdown">
                          <a href="" class="dropdown-toggle profile waves-effect waves-lightx" data-toggle="dropdown" aria-expanded="true"> <img src="<?php echo get_user()["avatar"] ?>" alt="user-img" class="img-circle"> <span class="profile-username"><?php echo get_user()["name"] ?> <br/> <small><?php echo get_user()['user_type'] ?></small> </span> </a>
                          <ul class="dropdown-menu">
                              <li><a href="<?php echo base_url() ?>dashboard/table/my_profile">My Profile</a>
                              </li>
                              <!-- <li><a href="javascript:void(0)"><span class="badge badge-success pull-right">5</span> Settings </a>
                              </li>
                              <li><a href="javascript:void(0)"> Lock screen</a>
                              </li> -->
                              <li class="divider"></li>
                              <li><a href="<?php echo base_url() ?>user_management/logout"> Logout</a>
                              </li>
                          </ul>
                      </li>
                  </ul>
              </div>
          </div>
      </div>
  </div>
