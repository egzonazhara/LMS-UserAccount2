<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pyetjet extends CI_Controller {

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
    // Manage Quizes
  public function quizes($course_id = "", $action = "", $quiz_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($action == 'add') {
      $this->crud_model->add_quiz($course_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
    }
    elseif ($action == 'edit') {
      $this->crud_model->edit_quiz($quiz_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
    }
    elseif ($action == 'delete') {
      $this->crud_model->delete_section($course_id, $quiz_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
    }
    redirect(site_url('course/course/course_form/course_edit/'.$course_id));
  }

  // Manage Quize Questions
  public function quiz_questions($quiz_id = "", $action = "", $question_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

    if ($action == 'add') {
      $response = $this->crud_model->add_quiz_questions($quiz_id);
      echo $response;
    }

    elseif ($action == 'edit') {
      $response = $this->crud_model->update_quiz_questions($question_id);
      echo $response;
    }

    elseif ($action == 'delete') {
      $response = $this->crud_model->delete_quiz_question($question_id);
      $this->session->set_flashdata('flash_message', get_phrase('question_has_been_deleted'));
      redirect(site_url('course/course/course_form/course_edit/'.$quiz_details['course_id']));
    }
  }
  function manage_multiple_choices_options() {
    $page_data['number_of_options'] = $this->input->post('number_of_options');
    $this->load->view('backend/admin/manage_multiple_choices_options', $page_data);
  }
 
}