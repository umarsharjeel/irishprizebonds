<?php
class Antelope extends CI_Model {
  public function __construct()
  {
    parent::__construct();
  }

  public function user_management($xcrud){
    $xcrud->table('antelope_users');
    $xcrud->unset_remove(true,'username','=','superadmin');
    $xcrud->change_type('password', 'password', 'md5', 16);
    $xcrud->change_type('avatar','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true)); // auto-crop
    $xcrud->set_attr('permissions',array('class'=>'permissions_list'));
    $xcrud->change_type('permissions','multiselect','',get_menus_for_user_management());
    return $xcrud->render();
  }

  public function draws($xcrud){
    $xcrud->table('draws')->order_by('draw_date','desc');
    $xcrud->columns('draw_date,is_jackpot,total_prize_fund,total_prizes_count,published,auto_import');
    $xcrud->label('draw_date','Draw Date');
    $xcrud->label('is_jackpot','Jackpot Draw?');
    $xcrud->label('total_prize_fund','Total Prize Fund (€)');
    $xcrud->label('total_prizes_count','Total Prizes');
    $xcrud->label('auto_import','Auto-Import?');

    // xCRUD's checkbox markup nests <input> inside <label> instead of as its
    // sibling, which this site's custom checkbox CSS (built for the sibling
    // pattern, see assets/css/style.css) can't handle — the real input ends
    // up unreachable to clicks. A Yes/No dropdown sidesteps that whole
    // rendering path since it's a plain <select>.
    $yes_no = array(1 => 'Yes', 0 => 'No');
    $xcrud->change_type('is_jackpot', 'select', '', $yes_no);
    $xcrud->change_type('published', 'select', '', $yes_no);
    $xcrud->change_type('auto_import', 'select', '', $yes_no);

    $tiers = $xcrud->nested_table('Prize Tiers','id','draw_prize_tiers','draw_id');
    $tiers->fields('prize_value,prize_count,sort_order');
    $tiers->columns('prize_value,prize_count,sort_order');
    $tiers->label('prize_value','Prize Value (€)')->label('prize_count','Number of Prizes')->label('sort_order','Sort Order');
    $tiers->order_by('sort_order');

    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  // Articles are created by Dashboard > Articles > Publish Jackpot Article (generated from
  // the draw data), so there is no "add" here: this is for tidying them afterwards.
  // Slug and draw link are read-only because changing them would break links already shared.
  // Deleting an article lets the publish action generate it again from scratch.
  public function posts($xcrud){
    if (!$this->db->table_exists('posts')) {
      return "<div class='alert alert-warning'>The <strong>posts</strong> table doesn't exist yet. Run <code>database/2026-11-posts.sql</code> on the database first (the SQL is also shown on the Publish Jackpot Article page).</div>";
    }
    $xcrud->table('posts')->order_by('published_at','desc');
    $xcrud->columns('title,status,published_at');
    $xcrud->fields('title,slug,summary,body,editor_note,status,published_at');
    $xcrud->label('title','Title')->label('slug','URL slug')->label('summary','Meta description (aim for 155 characters or fewer)');
    $xcrud->label('body','Article (HTML)')->label('editor_note',"Editor's note (shown under the article; plain text)");
    $xcrud->label('status','Status')->label('published_at','Published');
    $xcrud->change_type('status', 'select', 'published', array('published' => 'Published', 'draft' => 'Draft (hidden from the site)'));
    // Plain textareas, not xCRUD's rich-text editor: the visual editor would strip the table classes
    // and spacing the article relies on when it saves, and the editor's note is plain text anyway.
    $xcrud->no_editor('body,editor_note');
    $xcrud->readonly('slug');
    $xcrud->validation_required('title');
    $xcrud->unset_add();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  public function locations($xcrud){
    $xcrud->table('locations')->order_by('name');
    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  public function draw_winners($xcrud){
    $xcrud->table('draw_winners')->order_by('id','desc');
    $xcrud->relation('draw_id','draws','id','draw_date');
    $xcrud->relation('location_id','locations','id','name');
    $xcrud->label('draw_id','Draw')->label('location_id','Location')->label('bond_number','Bond Number')->label('prize_value','Prize Value (€)');
    $xcrud->unset_add();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  public function import_progress($xcrud){
    $xcrud->table('draw_import_progress')->order_by('draw_id','desc')->order_by('prize_value','desc');
    $xcrud->relation('draw_id','draws','id','draw_date');
    $xcrud->label('draw_id','Draw')->label('prize_value','Prize Value (€)')->label('total_count','Total Count')->label('total_pages','Total Pages')->label('next_page','Next Page')->label('done','Done?');
    $xcrud->unset_add();
    $xcrud->unset_edit();
    $xcrud->unset_remove();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  public function contacts($xcrud){
    $xcrud->table('contacts')->order_by('message_time','desc');
    $xcrud->label('message_time','Received')->label('ip_address','IP Address');
    $xcrud->unset_add();
    $xcrud->unset_edit();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    return $xcrud->render();
  }

  public function my_profile($xcrud){
    $xcrud->table('antelope_users');
    $xcrud->where('id =', get_user()["id"]);
    $xcrud->unset_remove();
    $xcrud->unset_add();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    $xcrud->unset_search();
    $xcrud->unset_pagination();
    $xcrud->unset_limitlist();
    $xcrud->unset_sortable();
    $xcrud->unset_list();
    $xcrud->columns('user_type,permissions', true);
    $xcrud->fields('user_type,permissions', true);
    $xcrud->change_type('password', 'password', 'md5', 16);
    $xcrud->change_type('avatar','image','',array('width'=>400, 'height'=>400,'ratio'=>1.0, 'manual_crop'=>true)); // auto-crop
    return $xcrud->render('edit', get_user()["id"]);
  }

}
