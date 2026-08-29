<div class="content-page">
            <div class="content">
                <div class="">
                    <div class="page-header-title">
                        <h4 class="page-title">Dashboad Overview</h4>
                    </div>
                </div>
                <div class="page-content-wrapper ">
                    <div class="container">
                      <div class="row">

                        <?php foreach($boxes as $box): ?>

                          <div class="col-sm-12 col-lg-<?php echo $box["width"] ?>  col-md-<?php echo $box["width"] ?>">
                             <div class="panel text-center">
                                <div class="panel-heading">
                                   <h4 class="panel-title text-muted font-light"><?php echo $box["box_title"] ?></h4>
                                </div>
                                <div class="panel-body p-t-10">
                                   <h2 class="m-t-0 m-b-15">
                                     <i class="mdi mdi-arrow-up-bold-circle-outline text-primary m-r-10"></i>
                                     <b><?php echo $box["box_count"] ?></b></h2>
                                   <!-- <p class="text-muted m-b-0 m-t-20"><b>48%</b> From Last 24 Hours</p> -->
                                </div>
                             </div>
                          </div>

                        <?php endforeach; ?>

                      </div>

                      <div class="row">

                        <?php foreach($charts as $index => $chart): ?>


                          <div class="col-sm-12 col-lg-<?php echo $chart["width"] ?>  col-md-<?php echo $chart["width"] ?>">
                             <div class="panel text-center">


                                <div class="panel-body p-t-10">

                                    <div id="container_<?php echo $index ?>" style="width:100%; height: 300px;"></div>

                                </div>
                             </div>
                          </div>



<script>
$(function () {
    var myChart = Highcharts.chart('container_<?php echo $index ?>', {
        chart: {
            type: '<?php echo $chart["type"] ?>'
        },
        title: {
            text: '<?php echo $chart["title"] ?>'
        },
        xAxis: {
            categories: <?php echo json_encode($chart["categories"]) ?>
        },
        yAxis: {
            title: {
                text: '<?php echo $chart["y_axis_title"] ?>'
            }
        },
        series: <?php echo json_encode($chart["series"]) ?>

    });
});
</script>



                        <?php endforeach; ?>

                      </div>
                    </div>
                </div>
            </div>
            <footer class="footer"> &copy; <?php echo date("Y"); ?> <?php echo antelope_config()["antelope_brand_name"] ?> - All Rights Reserved. </footer>
        </div>
