<?php
$db = new PDO('sqlite:database/cliniccare.sqlite');

echo 'patients=' . $db->query('SELECT COUNT(*) FROM patients')->fetchColumn() . PHP_EOL;
echo 'appointments=' . $db->query('SELECT COUNT(*) FROM appointments')->fetchColumn() . PHP_EOL;
