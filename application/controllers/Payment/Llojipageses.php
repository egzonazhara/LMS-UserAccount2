<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Llojipageses extends CI_Controller {

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

    function invoice($payment_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $page_data['page_name'] = 'invoice';
    $page_data['payment_details'] = $this->crud_model->get_payment_details_by_id($payment_id);
    $page_data['page_title'] = get_phrase('invoice');
    $this->load->view('backend/index', $page_data);
  }

  public function payment_history_delete($param1 = "", $redirect_to = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $this->crud_model->delete_payment_history($param1);
    $this->session->set_flashdata('flash_message', get_phrase('data_deleted_successfully'));
    redirect(site_url('payment/llojipageses/'.$redirect_to), 'refresh');
  }

 public function paypal_checkout_for_instructor_revenue() {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    $page_data['amount_to_pay']         = $this->input->post('amount_to_pay');
    $page_data['payment_id']            = $this->input->post('payment_id');
    $page_data['course_title']          = $this->input->post('course_title');
    $page_data['instructor_name']       = $this->input->post('instructor_name');
    $page_data['production_client_id']  = $this->input->post('production_client_id');
    $this->load->view('backend/admin/paypal_checkout_for_instructor_revenue', $page_data);
  }

  public function stripe_checkout_for_instructor_revenue() {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    $page_data['amount_to_pay']    = $this->input->post('amount_to_pay');
    $page_data['payment_id']       = $this->input->post('payment_id');
    $page_data['course_title']     = $this->input->post('course_title');
    $page_data['instructor_name']  = $this->input->post('instructor_name');
    $page_data['public_live_key']  = $this->input->post('public_live_key');
    $page_data['secret_live_key']  = $this->input->post('secret_live_key');
    $this->load->view('backend/admin/stripe_checkout_for_instructor_revenue', $page_data);
  }

  public function payment_success($payment_type = "", $payment_id = "") {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');

    if ($payment_type == 'stripe') {
      $token_id = $this->input->post('stripeToken');
      $payment_details = $this->db->get_where('payment', array('id' => $payment_id))->row_array();
      $instructor_id = $payment_details['user_id'];
      $instructor_data = $this->db->get_where('users', array('id' => $instructor_id))->row_array();
      $stripe_keys = json_decode($instructor_data['stripe_keys'], true);
      $this->payment_model->stripe_payment($token_id, $this->session->userdata('user_id'), $payment_details['instructor_revenue'], $stripe_keys[0]['secret_live_key']);
    }
    $this->crud_model->update_instructor_payment_status($payment_id);
    $this->session->set_flashdata('flash_message', get_phrase('instructor_payment_has_been_done'));
    redirect(site_url('administratori/enrollment/instructor_revenue'), 'refresh');
  }
 public function payment_settings($param1 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'paypal_settings') {
            $this->user_model->update_instructor_paypal_settings($this->session->userdata('user_id'));
            redirect(site_url('teacher/teacherr/payment_settings'), 'refresh');
        }
        if ($param1 == 'stripe_settings') {
            $this->user_model->update_instructor_stripe_settings($this->session->userdata('user_id'));
            redirect(site_url('teacher/teacherr/payment_settings'), 'refresh');
        }

        $page_data['page_name'] = 'payment_settings';
        $page_data['page_title'] = get_phrase('payment_settings');
        $this->load->view('backend/index', $page_data);
    }

    
    function invoice($payment_id = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name'] = 'invoice';
        $page_data['payment_details'] = $this->crud_model->get_payment_details_by_id($payment_id);
        $page_data['page_title'] = get_phrase('invoice');
        $this->load->view('backend/index', $page_data);
    }
    
   public function paypal_checkout() {
        if ($this->session->userdata('user_login') != 1)
        redirect('home', 'refresh');

        $total_price_of_checking_out  = $this->input->post('total_price_of_checking_out');
        $page_data['user_details']    = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        $page_data['amount_to_pay']   = $total_price_of_checking_out;
        $this->load->view('frontend/'.get_frontend_settings('theme').'/paypal_checkout', $page_data);
    }

    public function stripe_checkout() {
        if ($this->session->userdata('user_login') != 1)
        redirect('home', 'refresh');

        $total_price_of_checking_out  = $this->input->post('total_price_of_checking_out');
        $page_data['user_details']    = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        $page_data['amount_to_pay']   = $total_price_of_checking_out;
        $this->load->view('frontend/'.get_frontend_settings('theme').'/stripe_checkout', $page_data);
    }

    public function payment_success($method = "", $user_id = "", $amount_paid = "") {
        if ($method == 'stripe') {
            $token_id = $this->input->post('stripeToken');
            $stripe_keys = get_settings('stripe_keys');
            $values = json_decode($stripe_keys);
            if ($values[0]->testmode == 'on') {
                $public_key = $values[0]->public_key;
                $secret_key = $values[0]->secret_key;
            } else {
                $public_key = $values[0]->public_live_key;
                $secret_key = $values[0]->secret_live_key;
            }
            $this->payment_model->stripe_payment($token_id, $user_id, $amount_paid, $secret_key);
        }

        $this->crud_model->enrol_student($user_id);
        $this->crud_model->course_purchase($user_id, $method, $amount_paid);
        $this->session->set_userdata('cart_items', array());
        $this->session->set_flashdata('flash_message', get_phrase('payment_successfully_done'));
        redirect('home', 'refresh');
    }
}