PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'staff' CHECK (role IN ('admin','staff')),
    status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT NULL
);

CREATE TABLE patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new','contacted','scheduled','closed')),
    source TEXT NOT NULL DEFAULT 'phone' CHECK (source IN ('public_form','phone','facebook','referral','walk_in')),
    note TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT NULL,
    UNIQUE (email)
);

CREATE INDEX idx_patients_created_at ON patients (created_at);
CREATE INDEX idx_patients_status_created_at ON patients (status, created_at);
CREATE INDEX idx_patients_source_created_at ON patients (source, created_at);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_code TEXT NOT NULL,
    patient_name TEXT NOT NULL,
    patient_email TEXT DEFAULT NULL,
    appointment_date TEXT NOT NULL,
    service_type TEXT NOT NULL,
    fee_amount REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','confirmed','completed','cancelled')),
    note TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT NULL,
    UNIQUE (appointment_code)
);

CREATE INDEX idx_appointments_created_at ON appointments (created_at);
CREATE INDEX idx_appointments_date ON appointments (appointment_date);
CREATE INDEX idx_appointments_status_created_at ON appointments (status, created_at);
CREATE INDEX idx_appointments_patient_email ON appointments (patient_email);
