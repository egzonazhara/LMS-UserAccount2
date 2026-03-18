<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
        $this->load->database();
        $this->load->library('session');
        // $this->load->library('stripe');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        if (!$this->session->userdata('cart_items')) {
            $this->session->set_userdata('cart_items', array());
        }
    }

    public function index() {
        $this->home();
    }

    public function home() {
        $page_data['page_name'] = "home";
        $page_data['page_title'] = get_phrase('home');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }
     public function system_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('profile'), 'refresh');
    }

    if ($param1 == 'system_update') {
      $this->crud_model->update_system_settings();
      $this->session->set_flashdata('flash_message', get_phrase('system_settings_updated'));
      redirect(site_url('administratori/settings/system_settings'), 'refresh');
    }

    if ($param1 == 'logo_upload') {
      move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/backend/logo.png');
      $this->session->set_flashdata('flash_message', get_phrase('backend_logo_updated'));
      redirect(site_url('administratori/settings/system_settings'), 'refresh');
    }

    if ($param1 == 'favicon_upload') {
      move_uploaded_file($_FILES['favicon']['tmp_name'], 'assets/favicon.png');
      $this->session->set_flashdata('flash_message', get_phrase('favicon_updated'));
      redirect(site_url('administratori/settings/system_settings'), 'refresh');
    }

    $page_data['languages']  = $this->get_all_languages();
    $page_data['page_name'] = 'system_settings';
    $page_data['page_title'] = get_phrase('system_settings');
    $this->load->view('backend/index', $page_data);
  }

  public function frontend_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'frontend_update') {
      $this->crud_model->update_frontend_settings();
      $this->session->set_flashdata('flash_message', get_phrase('frontend_settings_updated'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }

    if ($param1 == 'banner_image_update') {
      $this->crud_model->update_frontend_banner();
      $this->session->set_flashdata('flash_message', get_phrase('banner_image_update'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }
    if ($param1 == 'light_logo') {
      $this->crud_model->update_light_logo();
      $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }
    if ($param1 == 'dark_logo') {
      $this->crud_model->update_dark_logo();
      $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }
    if ($param1 == 'small_logo') {
      $this->crud_model->update_small_logo();
      $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }
    if ($param1 == 'favicon') {
      $this->crud_model->update_favicon();
      $this->session->set_flashdata('flash_message', get_phrase('favicon_updated'));
      redirect(site_url('administratori/settings/frontend_settings'), 'refresh');
    }

    $page_data['page_name'] = 'frontend_settings';
    $page_data['page_title'] = get_phrase('frontend_settings');
    $this->load->view('backend/index', $page_data);
  }
  function get_all_languages() {
    $language_files = array();
    $all_files = $this->get_list_of_language_files();
    foreach ($all_files as $file) {
      $info = pathinfo($file);
      if( isset($info['extension']) && strtolower($info['extension']) == 'json') {
        $file_name = explode('.json', $info['basename']);
        array_push($language_files, $file_name[0]);
      }
    }
    return $language_files;
  }
   function get_list_of_language_files($dir = APPPATH.'/language', &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }

  function get_list_of_directories_and_files($dir = APPPATH, &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }
  public function manage_language($param1 = '', $param2 = '', $param3 = ''){
    if ($param1 == 'add_language') {
      saveDefaultJSONFile($this->input->post('language'));
      $this->session->set_flashdata('flash_message', get_phrase('language_added_successfully'));
      redirect(site_url('administratori/settings/manage_language'), 'refresh');
    }
    if ($param1 == 'add_phrase') {
      $new_phrase = get_phrase($this->input->post('phrase'));
      $this->session->set_flashdata('flash_message', $new_phrase.' '.get_phrase('has_been_added_successfully'));
      redirect(site_url('administratori/settings/manage_language'), 'refresh');
    }

    if ($param1 == 'edit_phrase') {
      $page_data['edit_profile'] = $param2;
    }

    $page_data['languages']       = $this->get_all_languages();
    $page_data['page_name']       = 'manage_language';
    $page_data['page_title']      = get_phrase('multi_language_settings');
    $this->load->view('backend/index', $page_data);
  }

  public function payment_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'system_currency') {
      $this->crud_model->update_system_currency();
      redirect(site_url('administratori/settings/payment_settings'), 'refresh');
    }
    if ($param1 == 'paypal_settings') {
      $this->crud_model->update_paypal_settings();
      redirect(site_url('administratori/settings/payment_settings'), 'refresh');
    }
    if ($param1 == 'stripe_settings') {
      $this->crud_model->update_stripe_settings();
      redirect(site_url('administratori/settings/payment_settings'), 'refresh');
    }

    $page_data['page_name'] = 'payment_settings';
    $page_data['page_title'] = get_phrase('payment_settings');
    $this->load->view('backend/index', $page_data);
  }

  public function smtp_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'update') {
      $this->crud_model->update_smtp_settings();
      $this->session->set_flashdata('flash_message', get_phrase('smtp_settings_updated_successfully'));
      redirect(site_url('administratori/settings/smtp_settings'), 'refresh');
    }

    $page_data['page_name'] = 'smtp_settings';
    $page_data['page_title'] = get_phrase('smtp_settings');
    $this->load->view('backend/index', $page_data);
  }
  public function instructor_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == 'update') {
      $this->crud_model->update_instructor_settings();
      $this->session->set_flashdata('flash_message', get_phrase('instructor_settings_updated'));
      redirect(site_url('administratori/settings/instructor_settings'), 'refresh');
    }

    $page_data['page_name'] = 'instructor_settings';
    $page_data['page_title'] = get_phrase('instructor_settings');
    $this->load->view('backend/index', $page_data);
  }
function about() {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    $page_data['application_details'] = $this->crud_model->get_application_details();
    $page_data['page_name']  = 'about';
    $page_data['page_title'] = get_phrase('about');
    $this->load->view('backend/index', $page_data);
  }
  // software themes page
  function themes() {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    $page_data['page_name']  = 'themes';
    $page_data['page_title'] = get_phrase('themes');
    $this->load->view('backend/index', $page_data);
  }
  // software mobile app page
  function mobile_app() {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    $page_data['page_name']  = 'mobile_app';
    $page_data['page_title'] = get_phrase('mobile_app');
    $this->load->view('backend/index', $page_data);
  }

}