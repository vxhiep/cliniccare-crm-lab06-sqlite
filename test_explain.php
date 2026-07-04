<?php

$db = new PDO('sqlite:' . __DIR__ . '/database/cliniccare.sqlite');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$queries = [
    'Patients list query' => "
        EXPLAIN QUERY PLAN
        SELECT id, name, email, phone, status, source, created_at
        FROM patients
        WHERE name LIKE '%Patient%'
           OR email LIKE '%Patient%'
           OR phone LIKE '%Patient%'
        ORDER BY created_at DESC
        LIMIT 10 OFFSET 0
    ",
    'Appointments list query' => "
        EXPLAIN QUERY PLAN
        SELECT id, appointment_code, patient_name, patient_email,
               appointment_date, service_type, fee_amount, status, created_at
        FROM appointments
        WHERE appointment_code LIKE '%APT%'
           OR patient_name LIKE '%APT%'
           OR patient_email LIKE '%APT%'
           OR service_type LIKE '%APT%'
        ORDER BY appointment_date DESC
        LIMIT 10 OFFSET 0
    ",
];

foreach ($queries as $title => $sql) {
    echo "\n=== {$title} ===\n";
    foreach ($db->query($sql) as $row) {
        print_r($row);
    }
}