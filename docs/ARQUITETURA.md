# 🏗️ Arquitetura - Clínica Popular

## Visão Geral
Sistema desenvolvido com **Domain-Driven Design (DDD)** com separação clara entre Domain, Application e Infrastructure.

---

## 📁 Estrutura de Pastas
app/
├── Domain/                    # Lógica de negócio pura
│   ├── Patient/               # ✅ COMPLETO
│   │   ├── Models/
│   │   ├── Enums/
│   │   ├── DataTransferObjects/
│   │   ├── Actions/
│   │   ├── Events/
│   │   └── Exceptions/
│   ├── Specialty/             # ✅ COMPLETO
│   ├── Doctor/                # ✅ COMPLETO
│   ├── Shift/                 # ✅ COMPLETO
│   ├── Appointment/           # ✅ COMPLETO
│   ├── Auth/                  # ✅ COMPLETO
│   ├── WaitingList/           # ✅ COMPLETO
│   ├── Payment/               # ⏳ FUTURO
│   └── Notification/          # ⏳ FUTURO
├── Application/               # Casos de uso (ainda não implementado)
│   └── (Será estruturado)
└── Infrastructure/            # Implementações técnicas (ainda não isolado)
└── (Será refatorado)
database/
├── factories/                 # Factories dos Models
├── migrations/                # Migrations do banco
└── seeders/                   # Seeders iniciais
routes/
├── api.php                    # Rotas da API
tests/
├── Feature/
│   └── Api/                   # ✅ Testes dos Controllers
│       ├── Auth/
│       ├── Patient/
│       ├── Specialty/
│       ├── Doctor/
│       ├── Shift/
│       ├── Appointment/
│       └── WaitingList/
└── Unit/                      # ⏳ Testes unitários (futuro)

---

## 🎯 Padrões de Design

### Domain-Driven Design (DDD)

Cada domínio segue a mesma estrutura:
Domain/
├── NomeDominio/
│   ├── Models/
│   │   └── NomeDominio.php        # Model principal (Eloquent)
│   ├── Enums/
│   │   └── NomeDominioStatus.php   # Status/Estados possíveis
│   ├── DataTransferObjects/
│   │   └── NomeDominioData.php     # DTO para transferência de dados
│   ├── Actions/
│   │   ├── CreateNomeDominioAction.php
│   │   ├── UpdateNomeDominioAction.php
│   │   └── DeleteNomeDominioAction.php
│   ├── Events/
│   │   ├── NomeDominioCreated.php
│   │   ├── NomeDominioUpdated.php
│   │   └── NomeDominioDeleted.php
│   └── Exceptions/
│       ├── NomeDominioNotFoundException.php
│       └── InvalidNomeDominioException.php

### Actions Pattern

Toda lógica de negócio está em **Actions**:

```php
class CreatePatientAction {
    public function execute(PatientData $data): Patient {
        // Validações
        // Criar model
        // Disparar evento
        // Retornar model
    }
}
```

### Events

Eventos são disparados após ações bem-sucedidas:

```php
event(new PatientRegistered($patient));
event(new AppointmentCreated($appointment));
```

---

## 🗄️ Stack Tecnológica

- **Backend**: PHP 8.3 + Laravel 13.x
- **Database**: PostgreSQL 16
- **Cache**: Redis 7
- **Queue**: Laravel Horizon (futuro)
- **Real-time**: Laravel Reverb (futuro)
- **Autenticação**: Laravel Sanctum (tokens API)
- **Validação**: Spatie LaravelData
- **Testing**: Pest PHP

---

## 🔌 Integrations (Futuro)

- **WhatsApp**: Evolution API
- **Pagamentos**: Asaas API ou Mercado Pago
- **NFS-e**: API municipal
- **Frontend**: Livewire 3 + Alpine.js

---

## 📊 Fluxo de Dados
Request (API)
↓
Controller (Validação básica)
↓
Action (Lógica de negócio)
↓
Model (Persist no banco)
↓
Event (Notifica listeners)
↓
Response (JSON)

---

## 🔐 Segurança

- Validação de CPF via Mod 11
- UUIDs como primary keys (não sequenciais)
- Autenticação via tokens Sanctum
- Role-based access control (ADMIN, DOCTOR, RECEPTIONIST, PATIENT)
- Soft deletes (não deletar dados, apenas marcar como inativo)

---

## 📈 Escalabilidade

- Migrations para versionamento de schema
- Factories para testes
- Repositório único no GitHub
- Docker para ambiente consistente
- Pronto para CI/CD (GitHub Actions)

---

## 🧪 Testes

- **Feature Tests**: Testam fluxos completos (API, banco, eventos)
- **Unit Tests**: (A implementar) Testam Actions, Validations isoladamente
- Coverage: Todas as rotas API testadas
- Framework: Pest PHP (sintaxe mais limpa que PHPUnit)

---

## ⚙️ Convenções

- **Primary Keys**: UUID (Laravel\Illuminate\Support\Str::uuid())
- **Enums**: string-backed
- **Naming**: snake_case para DB, PascalCase para classes
- **Line endings**: LF
- **Encoding**: UTF-8
- **Timestamps**: created_at, updated_at (automáticos)