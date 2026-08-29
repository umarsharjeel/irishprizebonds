
<div class="left side-menu">
           <div class="sidebar-inner slimscrollleft">

               <div id="sidebar-menu">
                   <ul>
                     <?php


 foreach ($menus as $key => $value) {

     $select_this = false;

     if($value['url'] != null){
         $select_this = ($active_menu == $value["url"]) ? true : false;
     }

     if($value['sub_menus'] != null){


         $submenu_is_selected = array_search($active_menu, array_column($value['sub_menus'], 'url'));


         if(!is_bool($submenu_is_selected)){
             $select_this = true;
         }

     }


?>


<li class="<?php echo ($value["sub_menus"]) ? ' has_sub ' : ''; ?>">
  <a href="<?php echo ($value["url"]) ?  base_url(). $value["url"] . '/' : 'javascript:void(0);' ?>" class=" waves-effect <?php echo ($select_this) ? ' active subdrop' : ''; ?>">
    <i class="<?php echo $value["icon"] ?>"></i>
    <span> <?php echo $value["title"] ?></span>

    <?php echo ($value["sub_menus"]) ? '   <span class="pull-right"><i class="mdi mdi-plus"></i></span> ' : ''; ?>





  </a>


    <?php if ($value['sub_menus'] != null) {
       $sub_menus = $value["sub_menus"];
     ?>


     <ul class="list-unstyled">
         <?php foreach ($sub_menus as $key_submenu => $value_submenu) {
         $select_this_submenu = false;


              if($value_submenu['url'] != null){
                 $select_this_submenu = ($active_menu == $value_submenu["url"]) ? true : false;
             }
         ?>

         <li class="nav-item <?php echo ($select_this_submenu) ? ' active ' : ''; ?>">
             <a class=" waves-effect " href="<?php echo base_url(). $value_submenu["url"] ?>/">
                 <i class="<?php echo $value_submenu["icon"] ?>"></i>
                 <span class="title"><?php echo $value_submenu["title"] ?></span>
             </a>
         </li>
           <?php } ?>

     </ul>

     <?php } ?>

 </li>






<?php

 }

?>


                     </ul>
                   </div>
                 </div>
               </div>
