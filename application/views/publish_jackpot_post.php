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

                                <?php if (!$table_ok): ?>
                                    <div class="alert alert-warning">
                                        <strong>One-off setup needed.</strong> The <code>posts</code> table doesn't exist yet, so nothing can be published.
                                        Run this SQL on the database (it is also saved as <code>database/2026-11-posts.sql</code>), then reload this page:
                                    </div>
                                    <pre style="max-height:320px;overflow:auto;"><?php echo htmlspecialchars($create_sql) ?></pre>
                                <?php endif; ?>

                                <?php if ($result): ?>
                                    <?php if ($result['ok']): ?>
                                        <div class="alert alert-success">
                                            Published. <a href="<?php echo base_url() ?>news/<?php echo htmlspecialchars($result['slug']) ?>/" target="_blank" rel="noopener">View the article</a>
                                            &middot; <a href="<?php echo base_url() ?>dashboard/table/posts/">Edit it under All Articles</a>
                                        </div>
                                    <?php elseif (!empty($result['already'])): ?>
                                        <div class="alert alert-info">
                                            An article already exists for this draw, so nothing was changed.
                                            <a href="<?php echo base_url() ?>news/<?php echo htmlspecialchars($result['slug']) ?>/" target="_blank" rel="noopener">View it</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($result['error']) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <p class="text-muted">
                                    Writes and publishes the article for a monthly <strong>&euro;500,000 jackpot draw</strong> from the draw data already on the site
                                    (prize tiers, the winner and the earlier jackpots). Nothing is invented: no names, no quotes. If a draw already has an article, publishing it
                                    again does nothing. To rewrite one, delete it under <a href="<?php echo base_url() ?>dashboard/table/posts/">All Articles</a> and publish again.
                                </p>

                                <?php if (empty($overview)): ?>
                                    <div class="alert alert-info">There are no published jackpot draws yet.</div>
                                <?php elseif ($selected): ?>

                                    <h4>
                                        Jackpot draw of <?php echo date('j F Y', strtotime($selected->draw_date)) ?>
                                        <?php echo ((int) $selected_id === (int) $overview[0]->id) ? '<small class="text-muted">(latest)</small>' : '' ?>
                                    </h4>

                                    <?php if ($selected->post): ?>
                                        <div class="alert alert-info">
                                            This draw already has an article (<?php echo htmlspecialchars($selected->post->status) ?>):
                                            <a href="<?php echo base_url() ?>news/<?php echo htmlspecialchars($selected->post->slug) ?>/" target="_blank" rel="noopener">view it</a>
                                            &middot; <a href="<?php echo base_url() ?>dashboard/table/posts/">edit</a>.
                                        </div>
                                    <?php elseif ($preview && !$preview['ok']): ?>
                                        <div class="alert alert-warning"><strong>Not ready to publish:</strong> <?php echo htmlspecialchars($preview['error']) ?></div>
                                    <?php elseif ($preview): $pp = $preview['post']; ?>
                                        <p><strong>Preview</strong> (nothing is saved until you press Publish):</p>
                                        <div style="border:1px solid #ddd;border-radius:4px;padding:16px 20px;margin-bottom:16px;background:#fff;max-width:820px;">
                                            <h3 style="margin-top:0;"><?php echo htmlspecialchars($pp['title']) ?></h3>
                                            <style>
                                                .jp-preview table { border-collapse: collapse; margin-bottom: 12px; }
                                                .jp-preview th, .jp-preview td { border: 1px solid #ddd; padding: 4px 10px; text-align: left; }
                                            </style>
                                            <div class="jp-preview"><?php echo $pp['body'] ?></div>
                                        </div>
                                        <p class="text-muted">
                                            Page address: <code>/news/<?php echo htmlspecialchars($pp['slug']) ?>/</code><br>
                                            Meta description (<?php echo mb_strlen($pp['summary'], 'UTF-8') ?> characters): <?php echo htmlspecialchars($pp['summary']) ?>
                                        </p>
                                        <?php if ($table_ok): ?>
                                            <form method="post" action="<?php echo base_url() ?>dashboard/publish_jackpot_post/" onsubmit="return confirm('Publish this article on the live site?');">
                                                <input type="hidden" name="draw_id" value="<?php echo (int) $selected_id ?>">
                                                <button type="submit" name="do_publish" value="1" class="btn btn-primary">Publish article</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <hr>
                                    <h4>All jackpot draws</h4>
                                    <div class="table-responsive">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr><th>Draw</th><th>Winner data</th><th>Article</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($overview as $o): ?>
                                                    <tr<?php echo ((int) $o->id === (int) $selected_id) ? ' class="info"' : '' ?>>
                                                        <td><?php echo date('j F Y', strtotime($o->draw_date)) ?></td>
                                                        <td><?php echo $o->ready ? 'Ready' : htmlspecialchars($o->not_ready_reason) ?></td>
                                                        <td>
                                                            <?php if ($o->post): ?>
                                                                <?php echo htmlspecialchars($o->post->status) ?> &middot;
                                                                <a href="<?php echo base_url() ?>news/<?php echo htmlspecialchars($o->post->slug) ?>/" target="_blank" rel="noopener">view</a>
                                                            <?php else: ?>
                                                                <a href="<?php echo base_url() ?>dashboard/publish_jackpot_post/?draw_id=<?php echo (int) $o->id ?>">Preview</a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
