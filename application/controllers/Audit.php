<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Optional: add auth/role guard here
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('pagination');
    }

    public function index()
    {
        // --- Read filters ---
        $f_username = trim((string)$this->input->get('username', true));
        $f_action   = trim((string)$this->input->get('action', true)); // create/update/delete/login/logout/import/export/view/other
        $f_table    = trim((string)$this->input->get('table', true));
        $f_pk       = trim((string)$this->input->get('pk', true));
        $f_route    = trim((string)$this->input->get('route', true));
        $f_from     = trim((string)$this->input->get('from', true)); // YYYY-MM-DD
        $f_to       = trim((string)$this->input->get('to', true));   // YYYY-MM-DD

        $filters = compact('f_username','f_action','f_table','f_pk','f_route','f_from','f_to');

        // --- Base query with filters ---
        $this->db->from('audit_log');
        $this->_apply_filters($filters);

        // --- Pagination total ---
        $total_rows = $this->db->count_all_results('', false); // keep the builder for next get

        // --- Pagination setup ---
        $per_page = 50;
        $page = (int) $this->input->get('page');
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $per_page;

        $config = [
            'base_url'            => site_url('audit') . '?' . http_build_query(array_merge($this->_filters_as_query($filters), ['page'=>null])),
            'page_query_string'   => TRUE,
            'query_string_segment'=> 'page',
            'total_rows'          => $total_rows,
            'per_page'            => $per_page,
            'reuse_query_string'  => TRUE,
            'full_tag_open'       => '<ul class="pagination pagination-sm">',
            'full_tag_close'      => '</ul>',
            'num_tag_open'        => '<li class="page-item"><span class="page-link">',
            'num_tag_close'       => '</span></li>',
            'cur_tag_open'        => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close'       => '</span></li>',
            'prev_tag_open'       => '<li class="page-item"><span class="page-link">',
            'prev_tag_close'      => '</span></li>',
            'next_tag_open'       => '<li class="page-item"><span class="page-link">',
            'next_tag_close'      => '</span></li>',
            'first_tag_open'      => '<li class="page-item"><span class="page-link">',
            'first_tag_close'     => '</span></li>',
            'last_tag_open'       => '<li class="page-item"><span class="page-link">',
            'last_tag_close'      => '</span></li>',
        ];
        $this->pagination->initialize($config);

        // --- Fetch page ---
        $this->db->order_by('occurred_at', 'DESC');
        $this->db->limit($per_page, $offset);
        $rows = $this->db->get()->result();

        $data = [
            'rows'       => $rows,
            'total_rows' => $total_rows,
            'per_page'   => $per_page,
            'page'       => $page,
            'links'      => $this->pagination->create_links(),
            'filters'    => $filters
        ];

        $this->load->view('audit/index', $data);
    }

    public function export()
    {
        // Same filters as index()
        $f_username = trim((string)$this->input->get('username', true));
        $f_action   = trim((string)$this->input->get('action', true));
        $f_table    = trim((string)$this->input->get('table', true));
        $f_pk       = trim((string)$this->input->get('pk', true));
        $f_route    = trim((string)$this->input->get('route', true));
        $f_from     = trim((string)$this->input->get('from', true));
        $f_to       = trim((string)$this->input->get('to', true));

        $filters = compact('f_username','f_action','f_table','f_pk','f_route','f_from','f_to');

        $this->db->from('audit_log');
        $this->_apply_filters($filters);
        $this->db->order_by('occurred_at', 'DESC');
        $q = $this->db->get();

        $filename = 'audit_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['occurred_at','username','settingsID','action','table_name','pk_name','pk_value','route','ip_address','user_agent','note','before_json','after_json']);
        foreach ($q->result_array() as $r) {
            fputcsv($out, [
                $r['occurred_at'],
                $r['username'],
                $r['settingsID'],
                $r['action'],
                $r['table_name'],
                $r['pk_name'],
                $r['pk_value'],
                $r['route'],
                $r['ip_address'],
                $r['user_agent'],
                $r['note'],
                $r['before_json'],
                $r['after_json'],
            ]);
        }
        fclose($out);
        exit;
    }

    // ---------- helpers ----------
    private function _apply_filters(array $f)
    {
        if ($f['f_username'] !== '') $this->db->where('username', $f['f_username']);
        if ($f['f_action']   !== '') $this->db->where('action', $f['f_action']);
        if ($f['f_table']    !== '') $this->db->where('table_name', $f['f_table']);
        if ($f['f_pk']       !== '') {
            // matches either exact pk_value or "pk_name=pk_value"
            $this->db->group_start()
                     ->where('pk_value', $f['f_pk'])
                     ->or_where("CONCAT(COALESCE(pk_name,''),'=',COALESCE(pk_value,'')) =", $f['f_pk'])
                     ->group_end();
        }
        if ($f['f_route']    !== '') $this->db->like('route', $f['f_route']);
        if ($f['f_from']     !== '') $this->db->where('occurred_at >=', $f['f_from'].' 00:00:00');
        if ($f['f_to']       !== '') $this->db->where('occurred_at <=', $f['f_to'].' 23:59:59');
    }

    private function _filters_as_query(array $f)
    {
        $q = [];
        if ($f['f_username'] !== '') $q['username'] = $f['f_username'];
        if ($f['f_action']   !== '') $q['action']   = $f['f_action'];
        if ($f['f_table']    !== '') $q['table']    = $f['f_table'];
        if ($f['f_pk']       !== '') $q['pk']       = $f['f_pk'];
        if ($f['f_route']    !== '') $q['route']    = $f['f_route'];
        if ($f['f_from']     !== '') $q['from']     = $f['f_from'];
        if ($f['f_to']       !== '') $q['to']       = $f['f_to'];
        return $q;
    }
}
