<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends CI_Controller {

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
    //admin
 function message($param1 = 'message_home', $param2 = '', $param3 = '')
  {
    if ($this->session->userdata('admin_login') != 1)
    redirect(site_url('login'), 'refresh');
    if ($param1 == 'send_new') {
      $message_thread_code = $this->crud_model->send_new_private_message();
      $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
      redirect(site_url('messages/message/message/message_read/' . $message_thread_code), 'refresh');
    }

    if ($param1 == 'send_reply') {
      $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
      $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
      redirect(site_url('messages/message/message/message_read/' . $param2), 'refresh');
    }

    if ($param1 == 'message_read') {
      $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
      $this->crud_model->mark_thread_messages_read($param2);
    }

    $page_data['message_inner_page_name'] = $param1;
    $page_data['page_name']               = 'message';
    $page_data['page_title']              = get_phrase('private_messaging');
    $this->load->view('backend/index', $page_data);
  }
      //home
 public function my_messages($param1 = "", $param2 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('home'), 'refresh');
        }
        if ($param1 == 'read_message') {
            $page_data['message_thread_code'] = $param2;
        }
        elseif ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(site_url('messages/message/my_messages/read_message/' . $message_thread_code), 'refresh');
        }
        elseif ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(site_url('messages/message/my_messages/read_message/' . $param2), 'refresh');
        }
        $page_data['page_name'] = "my_messages";
        $page_data['page_title'] = get_phrase('my_messages');
        $this->load->view('frontend/'.get_frontend_settings('theme').'/index', $page_data);
    }

}