<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course extends CI_Controller {

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
    public function courses() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }


    $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
    $page_data['selected_instructor_id'] = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : "all";
    $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
    $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";
    $page_data['courses']                = $this->crud_model->filter_course_for_backend($page_data['selected_category_id'], $page_data['selected_instructor_id'], $page_data['selected_price'], $page_data['selected_status']);
    $page_data['status_wise_courses']    = $this->crud_model->get_status_wise_courses();
    $page_data['instructors']            = $this->user_model->get_instructor();
    $page_data['page_name']              = 'courses';
    $page_data['categories']             = $this->crud_model->get_categories();
    $page_data['page_title']             = get_phrase('active_courses');
    $this->load->view('backend/index', $page_data);
  }

  public function pending_courses() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }


    $page_data['page_name'] = 'pending_courses';
    $page_data['page_title'] = get_phrase('pending_courses');
    $this->load->view('backend/index', $page_data);
  }

  public function course_actions($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == "add") {
      $this->crud_model->add_course();
      redirect(site_url('course/course/courses'), 'refresh');

    }
    elseif ($param1 == "edit") {
      $this->crud_model->update_course($param2);
      redirect(site_url('course/course/courses'), 'refresh');

    }
    elseif ($param1 == 'delete') {
      $this->is_drafted_course($param2);
      $this->crud_model->delete_course($param2);
      redirect(site_url('course/course/courses'), 'refresh');
    }
  }


  public function course_form($param1 = "", $param2 = "") {

    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('profile'), 'refresh');
    }

    if ($param1 == 'add_course') {
      $page_data['languages']   = $this->get_all_languages();
      $page_data['categories'] = $this->crud_model->get_categories();
      $page_data['page_name'] = 'course_add';
      $page_data['page_title'] = get_phrase('add_course');
      $this->load->view('backend/index', $page_data);

    }elseif ($param1 == 'course_edit') {
      $this->is_drafted_course($param2);
      $page_data['page_name'] = 'course_edit';
      $page_data['course_id'] =  $param2;
      $page_data['page_title'] = get_phrase('edit_course');
      $page_data['languages']   = $this->get_all_languages();
      $page_data['categories'] = $this->crud_model->get_categories();
      $this->load->view('backend/index', $page_data);
    }
  }

  private function is_drafted_course($course_id){
    $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
    if ($course_details['status'] == 'draft') {
      $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
      redirect(site_url('course/course/courses'), 'refresh');
    }
  }

  public function change_course_status($updated_status = "") {
    $course_id = $this->input->post('course_id');
    $category_id = $this->input->post('category_id');
    $instructor_id = $this->input->post('instructor_id');
    $price = $this->input->post('price');
    $status = $this->input->post('status');
    if (isset($_POST['mail_subject']) && isset($_POST['mail_body'])) {
      $mail_subject = $this->input->post('mail_subject');
      $mail_body = $this->input->post('mail_body');
      $this->email_model->send_mail_on_course_status_changing($course_id, $mail_subject, $mail_body);
    }
    $this->crud_model->change_course_status($updated_status, $course_id);
    $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
    redirect(site_url('course/course/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
  }

  public function change_course_status_for_admin($updated_status = "", $course_id = "", $category_id = "", $status = "", $instructor_id = "", $price = "") {
    $this->crud_model->change_course_status($updated_status, $course_id);
    $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
    redirect(site_url('course/course/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
  }

  public function sections($param1 = "", $param2 = "", $param3 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param2 == 'add') {
      $this->crud_model->add_section($param1);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_added_successfully'));
    }
    elseif ($param2 == 'edit') {
      $this->crud_model->edit_section($param3);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
    }
    elseif ($param2 == 'delete') {
      $this->crud_model->delete_section($param1, $param3);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
    }
    redirect(site_url('course/course/course_form/course_edit/'.$param1));
  }
      public function my_courses_by_category() {
        $category_id = $this->input->post('category_id');
        $course_details = $this->crud_model->get_my_courses_by_category_id($category_id)->result_array();
        $page_data['my_courses'] = $course_details;
        $this->load->view('frontend/'.get_frontend_settings('theme').'/reload_my_courses', $page_data);
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
   public function ajax_get_section($course_id){
    $page_data['sections'] = $this->crud_model->get_section('course', $course_id)->result_array();
    return $this->load->view('backend/admin/ajax_get_section', $page_data);
  }
  public function ajax_sort_section() {
    $section_json = $this->input->post('itemJSON');
    $this->crud_model->sort_section($section_json);
  }
  public function watch_video($slugified_title = "", $lesson_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $lesson_details          = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();
    $page_data['provider']   = $lesson_details['video_type'];
    $page_data['video_url']  = $lesson_details['video_url'];
    $page_data['lesson_id']  = $lesson_id;
    $page_data['page_name']  = 'video_player';
    $page_data['page_title'] = get_phrase('video_player');
    $this->load->view('backend/index', $page_data);
  }
   public function preview($course_id = '') {
        if ($this->session->userdata('user_login') != 1)
        redirect(site_url('useraccount/login'), 'refresh');

        $this->is_the_course_belongs_to_current_instructor($course_id);
        if ($course_id > 0) {
            $courses = $this->crud_model->get_course_by_id($course_id);
            if ($courses->num_rows() > 0) {
                $course_details = $courses->row_array();
                redirect(site_url('home/lesson/'.slugify($course_details['title']).'/'.$course_details['id']), 'refresh');
            }
        }
        redirect(site_url('course/course/courses'), 'refresh');
    }
}
