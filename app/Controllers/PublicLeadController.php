<?php
class PublicLeadController
{
    public function __construct(private PatientService $patients)
    {
    }

    public function create(): void
    {
        render('public_leads/create', ['title' => 'Đăng ký tư vấn khám', 'errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        verify_csrf();
        $result = $this->patients->createPublicLead($_POST);
        if (!$result['success']) {
            render('public_leads/create', [
                'title' => 'Đăng ký tư vấn khám',
                'errors' => $result['errors'],
                'old' => $result['old'] ?? $_POST,
            ]);
        }

        flash('success', 'Thông tin tư vấn đã được gửi. Phòng khám sẽ liên hệ lại sớm.');
        redirect('/public-leads/create?sent=1');
    }
}
