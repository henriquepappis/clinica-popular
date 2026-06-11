# 🗄️ Database Schema - Clínica Popular

## Diagrama ER
USERS (Autenticação)
├── id (uuid) PK
├── name
├── email UNIQUE
├── password
├── role (enum: admin, doctor, receptionist, patient)
├── phone
├── is_active
├── created_at, updated_at
PATIENTS
├── id (uuid) PK
├── name
├── cpf UNIQUE
├── birth_date
├── phone
├── status (enum: active, inactive)
├── created_at, updated_at
SPECIALTIES
├── id (uuid) PK
├── name UNIQUE
├── description
├── status (enum: active, inactive)
├── created_at, updated_at
DOCTORS
├── id (uuid) PK
├── user_id (uuid) FK → USERS.id
├── crm UNIQUE
├── bio
├── status (enum: active, inactive, on_leave)
├── created_at, updated_at
DOCTOR_SPECIALTIES (Many-to-Many)
├── id (uuid) PK
├── doctor_id (uuid) FK → DOCTORS.id
├── specialty_id (uuid) FK → SPECIALTIES.id
├── UNIQUE (doctor_id, specialty_id)
SHIFTS
├── id (uuid) PK
├── name
├── period (enum: morning, afternoon, evening)
├── start_time
├── end_time
├── max_appointments
├── status (enum: active, inactive)
├── created_at, updated_at
├── UNIQUE (period, name)
APPOINTMENTS
├── id (uuid) PK
├── patient_id (uuid) FK → PATIENTS.id
├── doctor_id (uuid) FK → DOCTORS.id
├── shift_id (uuid) FK → SHIFTS.id
├── appointment_date (date)
├── appointment_time (time)
├── notes (text, nullable)
├── status (enum: scheduled, confirmed, completed, cancelled)
├── cancellation_reason (nullable)
├── created_at, updated_at
├── UNIQUE (doctor_id, appointment_date, appointment_time)
├── INDEX (patient_id, status, appointment_date)
WAITING_LISTS
├── id (uuid) PK
├── patient_id (uuid) FK → PATIENTS.id
├── specialty_id (uuid) FK → SPECIALTIES.id
├── priority (int 1-3)
├── status (enum: waiting, notified, cancelled)
├── reason (text, nullable)
├── added_at (timestamp)
├── notified_at (timestamp, nullable)
├── created_at, updated_at
├── UNIQUE (patient_id, specialty_id) where status = 'waiting'
├── INDEX (specialty_id, status, priority)
PRICES
├── id (uuid) PK
├── doctor_id (uuid) FK → DOCTORS.id (nullable)
├── specialty_id (uuid) FK → SPECIALTIES.id (nullable)
├── duration_minutes (int, nullable)
├── value (decimal 10,2)
├── created_at, updated_at
├── UNIQUE (doctor_id, specialty_id, duration_minutes)
├── INDEX (specialty_id, duration_minutes)
PAYMENTS
├── id (uuid) PK
├── appointment_id (uuid) FK → APPOINTMENTS.id
├── amount (decimal 10,2)
├── status (enum: pending, paid, failed, refunded)
├── payment_method (enum: pix, credit_card, debit_card, transfer)
├── transaction_id (string, nullable)
├── paid_at (timestamp, nullable)
├── created_at, updated_at
├── INDEX (appointment_id, status)

---

## Migrations Reference

### users (Laravel padrão + customizações)
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'patient',
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### patients
```sql
CREATE TABLE patients (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    birth_date DATE NOT NULL,
    phone VARCHAR(20),
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_cpf (cpf)
);
```

### specialties
```sql
CREATE TABLE specialties (
    id UUID PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status (status)
);
```

### doctors
```sql
CREATE TABLE doctors (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL,
    crm VARCHAR(50) UNIQUE NOT NULL,
    bio TEXT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status)
);
```

### doctor_specialties (Many-to-Many)
```sql
CREATE TABLE doctor_specialties (
    id UUID PRIMARY KEY,
    doctor_id UUID NOT NULL,
    specialty_id UUID NOT NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doctor_specialty (doctor_id, specialty_id)
);
```

### shifts
```sql
CREATE TABLE shifts (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    period VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_appointments INT DEFAULT 20,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_period_name (period, name),
    INDEX idx_status (status)
);
```

### appointments
```sql
CREATE TABLE appointments (
    id UUID PRIMARY KEY,
    patient_id UUID NOT NULL,
    doctor_id UUID NOT NULL,
    shift_id UUID NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'scheduled',
    cancellation_reason VARCHAR(500),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doctor_appointment (doctor_id, appointment_date, appointment_time),
    INDEX idx_patient (patient_id),
    INDEX idx_status (status),
    INDEX idx_date (appointment_date)
);
```

### waiting_lists
```sql
CREATE TABLE waiting_lists (
    id UUID PRIMARY KEY,
    patient_id UUID NOT NULL,
    specialty_id UUID NOT NULL,
    priority INT DEFAULT 1,
    status VARCHAR(50) DEFAULT 'waiting',
    reason TEXT,
    added_at TIMESTAMP NOT NULL,
    notified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_patient_specialty (patient_id, specialty_id),
    INDEX idx_specialty (specialty_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
);
```

### prices
```sql
CREATE TABLE prices (
    id UUID PRIMARY KEY,
    doctor_id UUID,
    specialty_id UUID,
    duration_minutes INT,
    value DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_price_config (doctor_id, specialty_id, duration_minutes),
    INDEX idx_specialty (specialty_id)
);
```

### payments (Futuro)
```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY,
    appointment_id UUID NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    paid_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    INDEX idx_appointment (appointment_id),
    INDEX idx_status (status)
);
```

---

## Índices Criados

| Tabela | Campo | Tipo | Motivo |
|--------|-------|------|--------|
| patients | cpf | UNIQUE | Validação de duplicação |
| patients | status | INDEX | Filtros comuns |
| doctors | status | INDEX | Filtros de disponibilidade |
| specialties | name | UNIQUE | Evitar duplicação |
| specialties | status | INDEX | Listar ativas |
| appointments | doctor_id, appointment_date, appointment_time | UNIQUE | Evitar double-booking |
| appointments | patient_id, status | INDEX | Histórico do paciente |
| appointments | appointment_date | INDEX | Filtros por data |
| waiting_lists | patient_id, specialty_id | UNIQUE | Um paciente por especialidade |
| waiting_lists | specialty_id, status | INDEX | Buscar interessados por especialidade |
| waiting_lists | priority | INDEX | Ordenar por urgência |
| prices | specialty_id, duration | UNIQUE | Config única por duração |

---

## Relacionamentos

### One-to-Many
- Patient → Appointments
- Patient → WaitingLists
- Doctor → Appointments
- Specialty → WaitingLists
- Specialty → Prices
- Shift → Appointments

### Many-to-Many
- Doctors ↔ Specialties (doctor_specialties)

### Foreign Keys
- Todas com ON DELETE CASCADE (ao deletar, deleta os relacionados)
- Integridade referencial garantida

---

## Data Types

- **UUID**: Primary keys + foreign keys
- **VARCHAR**: Strings curtas (names, emails)
- **TEXT**: Strings longas (descriptions, notes)
- **DATE**: Apenas data
- **TIME**: Apenas hora
- **TIMESTAMP**: Data + hora
- **DECIMAL(10,2)**: Valores monetários
- **BOOLEAN**: Flags de ativação
- **ENUM**: Estados e status

---

## Backup Strategy

- Daily backup via cron job
- Weekly backup to cloud storage (AWS S3/Digital Ocean)
- Monthly full backup archive
- Test restore quarterly

---

## Performance Tuning

- Query analysis com EXPLAIN PLAN
- Eager loading com `with()` para evitar N+1
- Pagination de resultados grandes
- Cache queries frequentes com Redis (futuro)
- Monitor slow queries via logs

---

## Constraints

Todas as constraints implementadas:
- NOT NULL onde obrigatório
- UNIQUE onde apropriado
- FOREIGN KEY com CASCADE delete
- CHECK constraints para enums (validação a nível DB)