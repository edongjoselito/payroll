<!DOCTYPE html>
<html lang="en">
<?php $this->load->view('includes/head'); ?>
<body>
<div id="wrapper">

  <?php $this->load->view('includes/top-nav-bar'); ?>
  <?php $this->load->view('includes/sidebar'); ?>


  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-md-12">
            <div class="page-title-box">
              <h4 class="page-title">Audit Log</h4>
              <div class="clearfix"></div>
              <hr style="border:0; height:2px; background:linear-gradient(to right,#4285F4 60%,#34A853 40%); opacity:.5;">
            </div>
          </div>
        </div>

        <style>
          .audit-card { background:#fff; border-radius:10px; box-shadow:0 1px 8px rgba(0,0,0,.05); padding:16px; }
          .audit-filters .form-control { height:36px; }
          .audit-table th, .audit-table td { font-size: 13px; vertical-align: top; }
          details summary { cursor: pointer; font-weight: 600; }
          pre.json { background:#0f172a; color:#e2e8f0; padding:10px; border-radius:8px; white-space:pre-wrap; word-break:break-word; }
          .muted { color:#6b7280; font-size:12px; }
        </style>

        <div class="row">
          <div class="col-md-12">
            <div class="audit-card">

              <form class="row gy-2 gx-2 audit-filters" method="get" action="<?= site_url('audit'); ?>">
                <div class="col-md-2">
                  <label class="muted">Username</label>
                  <input type="text" name="username" value="<?= html_escape($filters['f_username']) ?>" class="form-control" placeholder="admin">
                </div>
                <div class="col-md-2">
                  <label class="muted">Action</label>
                  <input type="text" name="action" value="<?= html_escape($filters['f_action']) ?>" class="form-control" placeholder="create/update/">
                </div>
                <div class="col-md-2">
                  <label class="muted">Table</label>
                  <input type="text" name="table" value="<?= html_escape($filters['f_table']) ?>" class="form-control" placeholder="attendance">
                </div>
                <div class="col-md-2">
                  <label class="muted">PK or pk=val</label>
                  <input type="text" name="pk" value="<?= html_escape($filters['f_pk']) ?>" class="form-control" placeholder="id=123">
                </div>
                <div class="col-md-2">
                  <label class="muted">Route</label>
                  <input type="text" name="route" value="<?= html_escape($filters['f_route']) ?>" class="form-control" placeholder="project/">
                </div>
                <div class="col-md-2">
                  <label class="muted">From</label>
                  <input type="date" name="from" value="<?= html_escape($filters['f_from']) ?>" class="form-control">
                </div>
                <div class="col-md-2 mt-2">
                  <label class="muted">To</label>
                  <input type="date" name="to" value="<?= html_escape($filters['f_to']) ?>" class="form-control">
                </div>
                <div class="col-md-8 mt-4">
                  <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                  <a class="btn btn-secondary btn-sm" href="<?= site_url('audit'); ?>">Reset</a>
                  <a class="btn btn-success btn-sm" href="<?= site_url('audit/export').'?'.http_build_query($this->input->get()); ?>">Export CSV</a>
                  <span class="muted ms-2">Showing <?= count($rows) ?> / <?= (int)$total_rows ?> rows</span>
                </div>
              </form>

              <div class="table-responsive mt-3">
                <table class="table table-hover audit-table">
                  <thead>
                    <tr>
                      <th style="min-width:150px;">When</th>
                      <th>User</th>
                      <th>Action</th>
                      <th>Table:PK</th>
                      <th>Route / IP</th>
                      <th>Before</th>
                      <th>After</th>
                      <th>Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($rows)): ?>
                      <tr><td colspan="8" class="text-center text-muted">No results</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                      <tr>
                        <td>
                          <?= html_escape($r->occurred_at) ?><br>
                          <span class="muted"><?= html_escape($r->user_agent) ?></span>
                        </td>
                        <td>
                          <?= html_escape($r->username) ?>
                          <?php if (!is_null($r->settingsID)): ?>
                            <br><span class="muted">settingsID: <?= (int)$r->settingsID ?></span>
                          <?php endif; ?>
                        </td>
                        <td><?= html_escape($r->action) ?></td>
                        <td>
                          <?= html_escape($r->table_name) ?>
                          <?php if ($r->pk_name): ?>
                            <br><span class="muted"><?= html_escape($r->pk_name) ?>=<?= html_escape($r->pk_value) ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div><?= html_escape($r->route) ?></div>
                          <div class="muted"><?= html_escape($r->ip_address) ?></div>
                        </td>
                        <td style="min-width:260px;">
                          <?php if ($r->before_json): ?>
                            <details>
                              <summary>View</summary>
                              <pre class="json"><?= htmlspecialchars(_pretty_json($r->before_json)) ?></pre>
                            </details>
                          <?php endif; ?>
                        </td>
                        <td style="min-width:260px;">
                          <?php if ($r->after_json): ?>
                            <details open>
                              <summary>View</summary>
                              <pre class="json"><?= htmlspecialchars(_pretty_json($r->after_json)) ?></pre>
                            </details>
                          <?php endif; ?>
                        </td>
                        <td><?= nl2br(html_escape($r->note)) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mt-2">
                <?= $links ?>
              </div>

            </div>
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </div><!-- /.content -->
  </div><!-- /.content-page -->

</div><!-- /#wrapper -->

<?php
function _pretty_json($raw) {
  $d = json_decode($raw, true);
  if ($d === null) return $raw; 
  return json_encode($d, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
}
?>
</body>
</html>
