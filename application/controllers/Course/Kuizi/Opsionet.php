 
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


 // AJAX PORTION

  // this function is responsible for managing multiple choice question
  function manage_multiple_choices_options() {
    $page_data['number_of_options'] = $this->input->post('number_of_options');
    $this->load->view('backend/adminn/manage_multiple_choices_options', $page_data);
  }
   public function ajax_sort_question() {
    $question_json = $this->input->post('itemJSON');
    $this->crud_model->sort_question($question_json);
  }}