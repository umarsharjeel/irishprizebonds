<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title"><?php echo $pageTitle ?></h4>
            </div>
        </div>
        <div class="page-content-wrapper ">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-primary">
                            <div class="panel-body">

                                <?php if ($result): ?>
                                    <?php if ($result['inserted'] > 0): ?>
                                        <div class="alert alert-success">
                                            Imported <strong><?php echo $result['inserted'] ?></strong> of <?php echo $result['total_lines'] ?> lines successfully.
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($result['errors'])): ?>
                                        <div class="alert alert-danger">
                                            <strong><?php echo count($result['errors']) ?> row(s) skipped:</strong>
                                            <ul style="max-height:250px;overflow-y:auto;margin-bottom:0;">
                                                <?php foreach ($result['errors'] as $err): ?>
                                                    <li><?php echo htmlspecialchars($err) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <p class="text-muted">
                                    Paste or upload winner rows for a single draw. Each row: <code>bond_number, prize_value, location</code>
                                    (location is optional; tab or comma separated; a header row is auto-detected and skipped).
                                </p>

                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Draw</label>
                                        <select name="draw_id" class="form-control" required>
                                            <option value="">-- select draw --</option>
                                            <?php foreach ($draws as $d): ?>
                                                <option value="<?php echo $d->id ?>"><?php echo $d->draw_date ?><?php echo $d->is_jackpot ? ' (Jackpot)' : '' ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Paste CSV data</label>
                                        <textarea name="paste_data" class="form-control" rows="10" placeholder="AHU176759,50000,Laois&#10;BDY424458,1000,Cork&#10;UV135216,1000,Monaghan"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>...or upload a CSV file</label>
                                        <input type="file" name="csv_file" accept=".csv,.txt">
                                    </div>

                                    <p class="text-muted">Known locations: <?php echo implode(', ', array_map(function($l){ return $l->name; }, $locations)) ?></p>

                                    <button type="submit" name="do_import" value="1" class="btn btn-primary">Import</button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer"> &copy; <?php echo date("Y"); ?> <?php echo antelope_config()["antelope_brand_name"] ?> - All Rights Reserved. </footer>
</div>
