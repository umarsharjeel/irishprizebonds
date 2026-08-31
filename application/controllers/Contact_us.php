<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_us extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('session'); // flashdata for the post-redirect-get form result
	}

	public function index()
	{
		$data['title'] = 'Contact Us | Irish Prize Bonds';
		$data['description'] = 'Get in touch with Irish Prize Bonds — questions, feedback, or spotted an error in our data.';
		$data['success'] = $this->session->flashdata('contact_success');
		$data['errors'] = $this->session->flashdata('contact_errors');
		$data['old'] = $this->session->flashdata('contact_old') ?: array('name' => '', 'email' => '', 'message' => '');
		$this->load->view('contact_us', $data);
	}

	public function submit()
	{
		$name = trim((string) $this->input->post('name'));
		$email = trim((string) $this->input->post('email'));
		$message = trim((string) $this->input->post('message'));
		$honeypot = trim((string) $this->input->post('website')); // hidden field; real users leave it blank

		$errors = array();

		if ($honeypot !== '') {
			// Silently pretend success to bots without touching the DB.
			$this->session->set_flashdata('contact_success', true);
			redirect('contact-us');
			return;
		}

		if ($name === '') {
			$errors[] = 'Please enter your name.';
		}
		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Please enter a valid email address.';
		}
		if ($message === '') {
			$errors[] = 'Please enter a message.';
		} elseif (strlen($message) > 5000) {
			$errors[] = 'Message is too long (5000 characters max).';
		}

		if (!empty($errors)) {
			$this->session->set_flashdata('contact_errors', $errors);
			$this->session->set_flashdata('contact_old', array('name' => $name, 'email' => $email, 'message' => $message));
			redirect('contact-us');
			return;
		}

		$this->load->database();
		$this->db->insert('contacts', array(
			'name' => $name,
			'email' => $email,
			'message' => $message,
			'ip_address' => $this->input->ip_address(),
		));

		$this->session->set_flashdata('contact_success', true);
		redirect('contact-us');
	}

}
