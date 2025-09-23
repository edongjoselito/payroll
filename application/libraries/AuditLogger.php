<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuditLogger {
  protected $CI;
  public function __construct() { $this->CI = &get_instance(); }

  public function log($action, $table=null, $pkName=null, $pkValue=null, $before=null, $after=null, $note=null) {
    $db = $this->CI->db;
    $username   = $this->CI->session->userdata('username') ?: 'guest';
    $settingsID = $this->CI->session->userdata('settingsID');
    $route      = uri_string();
    $ip         = $this->CI->input->ip_address();
    $ua         = substr($this->CI->input->user_agent(), 0, 255);

    $data = [
      'occurred_at' => date('Y-m-d H:i:s'),
      'username'    => $username,
      'settingsID'  => $settingsID,
      'action'      => $action,
      'table_name'  => $table ?: '',
      'pk_name'     => $pkName,
      'pk_value'    => $pkValue ? (string)$pkValue : null,
      'route'       => $route,
      'ip_address'  => $ip,
      'user_agent'  => $ua,
      'before_json' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
      'after_json'  => $after  ? json_encode($after,  JSON_UNESCAPED_UNICODE) : null,
      'note'        => $note
    ];
    $db->insert('audit_log', $data);
  }
}
