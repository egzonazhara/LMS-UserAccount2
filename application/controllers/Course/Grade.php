<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grade extends CI_Controller {

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
    public function lessons($course_id = "", $param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == 'add') {
      $this->crud_model->add_lesson();
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_added_successfully'));
      redirect('course/course/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'edit') {
      $this->crud_model->edit_lesson($param2);
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_updated_successfully'));
      redirect('course/course/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'delete') {
      $this->crud_model->delete_lesson($param2);
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
      redirect('course/course/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'filter') {
      redirect('course/ligjerata/lessons/'.$this->input->post('course_id'));
    }
    $page_data['page_name'] = 'lessons';
    $page_data['lessons'] = $this->crud_model->get_lessons('course', $course_id);
    $page_data['course_id'] = $course_id;
    $page_data['page_title'] = get_phrase('lessons');
    $this->load->view('backend/index', $page_data);
  } }