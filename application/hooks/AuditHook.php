<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuditHook {
  public function bindAuditContext() {
    $CI = &get_instance();
    $CI->load->database();

    // Only for state-changing requests (avoid noise on GET)
    $method = strtoupper($CI->input->server('REQUEST_METHOD') ?? 'GET');
    if (!in_array($method, ['POST','PUT','PATCH','DELETE'])) return;

    $username   = $CI->session->userdata('username') ?: 'guest';
    $settingsID = $CI->session->userdata('settingsID') ?: null;
    $route      = uri_string();
    $ip         = $CI->input->ip_address();
    $ua         = substr($CI->input->user_agent(), 0, 255);

    // Expose context to DB triggers via user variables
    $CI->db->query("SET @AUDIT_USER  = " . $CI->db->escape($username));
    $CI->db->query("SET @AUDIT_ROUTE = " . $CI->db->escape($route));
    $CI->db->query("SET @AUDIT_IP    = " . $CI->db->escape($ip));
    $CI->db->query("SET @AUDIT_UA    = " . $CI->db->escape($ua));
  }
}
