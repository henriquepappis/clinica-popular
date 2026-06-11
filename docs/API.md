# 🔌 API Reference - Clínica Popular

## Base URL
http://localhost/api

## Autenticação
Todas as rotas (exceto registro/login) requerem token Sanctum:
Authorization: Bearer {token}
Content-Type: application/json

---

## 📝 Rotas

### 🔐 **AUTH**

#### POST `/auth/register`
Registrar novo usuário

**Request:**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "role": "patient",
  "phone": "11999999999"
}
```

**Response:** `201 Created`
```json
{
  "message": "Usuário registrado com sucesso.",
  "user": {
    "id": "uuid",
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "patient",
    "phone": "11999999999"
  },
  "token": "token_aqui"
}
```

---

#### POST `/auth/login`
Fazer login

**Request:**
```json
{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Response:** `200 OK`
```json
{
  "message": "Login realizado com sucesso.",
  "user": { ... },
  "token": "token_aqui"
}
```

---

#### GET `/auth/me`
Obter dados do usuário autenticado

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "patient"
  }
}
```

---

#### POST `/auth/logout`
Fazer logout (deleta token)

**Response:** `200 OK`
```json
{
  "message": "Logout realizado com sucesso."
}
```

---

### 👥 **PACIENTES**

#### GET `/patients`
Listar todos os pacientes

**Query Params:**
- `page` (int): número da página
- `per_page` (int): itens por página
- `status` (string): filtrar por status (active, inactive)

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Maria Silva",
      "cpf": "12345678901",
      "birth_date": "1990-05-15",
      "phone": "11999999999",
      "status": "active"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 10
  }
}
```

---

#### POST `/patients`
Criar novo paciente

**Request:**
```json
{
  "name": "Maria Silva",
  "cpf": "12345678901",
  "birth_date": "1990-05-15",
  "phone": "11999999999"
}
```

**Response:** `201 Created`
```json
{
  "message": "Paciente criado com sucesso.",
  "data": {
    "id": "uuid",
    "name": "Maria Silva",
    "cpf": "12345678901",
    "status": "active"
  }
}
```

---

#### GET `/patients/{id}`
Obter dados de um paciente

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "name": "Maria Silva",
    "cpf": "12345678901",
    "birth_date": "1990-05-15",
    "phone": "11999999999",
    "status": "active",
    "created_at": "2026-06-05T10:00:00Z"
  }
}
```

---

#### PATCH `/patients/{id}`
Atualizar paciente

**Request:**
```json
{
  "name": "Maria Silva Updated",
  "phone": "11988888888"
}
```

**Response:** `200 OK`
```json
{
  "message": "Paciente atualizado com sucesso."
}
```

---

#### DELETE `/patients/{id}`
Desativar paciente (soft delete)

**Response:** `200 OK`
```json
{
  "message": "Paciente desativado com sucesso."
}
```

---

### 🏥 **ESPECIALIDADES**

#### GET `/specialties`
Listar especialidades ativas

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Cardiologia",
      "description": "Especialidade do coração",
      "status": "active"
    }
  ]
}
```

---

#### POST `/specialties`
Criar especialidade

**Request:**
```json
{
  "name": "Cardiologia",
  "description": "Especialidade do coração"
}
```

**Response:** `201 Created`

---

#### GET `/specialties/{id}`
Obter especialidade

**Response:** `200 OK`

---

#### PATCH `/specialties/{id}`
Atualizar especialidade

---

#### DELETE `/specialties/{id}`
Desativar especialidade

---

### 👨‍⚕️ **MÉDICOS**

#### GET `/doctors`
Listar médicos

**Query Params:**
- `specialty_id` (uuid): filtrar por especialidade
- `status` (string): filtrar por status

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Dr. João Silva",
      "crm": "123456",
      "email": "joao@clinic.com",
      "phone": "11999999999",
      "specialties": [
        {
          "id": "uuid",
          "name": "Cardiologia"
        }
      ],
      "status": "active"
    }
  ]
}
```

---

#### POST `/doctors`
Criar médico

**Request:**
```json
{
  "name": "Dr. João Silva",
  "crm": "123456",
  "email": "joao@clinic.com",
  "phone": "11999999999",
  "bio": "Especialista em cardiologia",
  "specialty_ids": ["uuid1", "uuid2"]
}
```

**Response:** `201 Created`

---

#### GET `/doctors/{id}`
Obter médico

---

#### PATCH `/doctors/{id}`
Atualizar médico

---

#### DELETE `/doctors/{id}`
Desativar médico

---

### 🕐 **TURNOS**

#### GET `/shifts`
Listar turnos ativos

#### POST `/shifts`
Criar turno

**Request:**
```json
{
  "name": "Manhã",
  "period": "morning",
  "start_time": "08:00",
  "end_time": "12:00",
  "max_appointments": 20
}
```

---

#### GET `/shifts/{id}`
Obter turno

---

#### PATCH `/shifts/{id}`
Atualizar turno

---

#### DELETE `/shifts/{id}`
Desativar turno

---

### 📅 **AGENDAMENTOS**

#### GET `/appointments`
Listar agendamentos

**Query Params:**
- `status` (string): SCHEDULED, CONFIRMED, COMPLETED, CANCELLED
- `date` (date): filtrar por data
- `doctor_id` (uuid): filtrar por médico
- `patient_id` (uuid): filtrar por paciente

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "patient": { "id": "uuid", "name": "Maria Silva" },
      "doctor": { "id": "uuid", "name": "Dr. João" },
      "appointment_date": "2026-07-22",
      "appointment_time": "10:00",
      "status": "scheduled",
      "notes": "Consulta de rotina"
    }
  ]
}
```

---

#### POST `/appointments`
Criar agendamento

**Request:**
```json
{
  "patient_id": "uuid",
  "doctor_id": "uuid",
  "shift_id": "uuid",
  "appointment_date": "2026-07-22",
  "appointment_time": "10:00",
  "notes": "Consulta de rotina"
}
```

**Response:** `201 Created`

---

#### GET `/appointments/{id}`
Obter agendamento

---

#### POST `/appointments/{id}/confirm`
Confirmar agendamento

**Response:** `200 OK`
```json
{
  "message": "Agendamento confirmado com sucesso.",
  "data": {
    "id": "uuid",
    "status": "confirmed"
  }
}
```

---

#### POST `/appointments/{id}/cancel`
Cancelar agendamento

**Request:**
```json
{
  "reason": "Paciente cancelou"
}
```

**Response:** `200 OK`

---

### 💰 **PREÇOS**

#### GET `/prices`
Listar preços configurados

**Query Params:**
- `doctor_id` (uuid): filtrar por médico
- `specialty_id` (uuid): filtrar por especialidade

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "type": "Por Especialidade",
      "doctor": null,
      "specialty": "Cardiologia",
      "duration_minutes": null,
      "value": "150.00"
    }
  ]
}
```

---

#### POST `/prices`
Criar configuração de preço (médico, especialidade ou duração)

**Request:**
```json
{
  "value": 150.00,
  "specialty_id": "uuid",
  "doctor_id": null,
  "duration_minutes": null
}
```

**Response:** `201 Created`
```json
{
  "message": "Preço criado com sucesso.",
  "data": {
    "id": "uuid",
    "type": "Por Especialidade",
    "specialty": "Cardiologia",
    "value": "150.00"
  }
}
```

**Erros:** `422` se nenhum alvo for informado ou se já existir preço para a combinação.

---

#### GET `/prices/{id}`
Obter configuração de preço

---

#### PATCH `/prices/{id}`
Atualizar valor do preço

**Request:**
```json
{
  "value": 180.00
}
```

**Response:** `200 OK`

---

#### DELETE `/prices/{id}`
Remover configuração de preço

**Response:** `200 OK`
```json
{
  "message": "Preço removido com sucesso."
}
```

---

### ⏳ **FILA DE ESPERA (WAITING LIST)**

#### GET `/waiting-lists`
Listar interessados

**Query Params:**
- `specialty_id` (uuid): filtrar por especialidade
- `status` (string): waiting, notified, cancelled

---

#### POST `/waiting-lists`
Registrar interesse

**Request:**
```json
{
  "patient_id": "uuid",
  "specialty_id": "uuid",
  "priority": 2,
  "reason": "Paciente em espera por consulta"
}
```

---

#### GET `/waiting-lists/{id}`
Obter interesse

---

#### POST `/waiting-lists/{id}/notify`
Notificar paciente (abre agenda)

**Response:** `200 OK`
```json
{
  "message": "Paciente notificado com sucesso."
}
```

---

#### DELETE `/waiting-lists/{id}`
Remover da fila

---

## 📊 Códigos de Resposta

- `200 OK` - Sucesso
- `201 Created` - Recurso criado
- `400 Bad Request` - Validação falhou
- `401 Unauthorized` - Não autenticado
- `403 Forbidden` - Sem permissão
- `404 Not Found` - Recurso não encontrado
- `422 Unprocessable Entity` - Lógica de negócio violada
- `500 Internal Server Error` - Erro do servidor

---

## 🔍 Filtros Comuns

Todas as rotas GET suportam:
- `page`: Número da página (padrão: 1)
- `per_page`: Itens por página (padrão: 15)
- `sort`: Campo para ordenação
- `order`: asc ou desc

Exemplo:
GET /appointments?page=2&per_page=20&sort=created_at&order=desc

