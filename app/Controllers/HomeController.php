<?php
class HomeController
{
    public function index(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        render('home', ['title' => 'ClinicCare Appointment CRM']);
    }
}
