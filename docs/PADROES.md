# 📌 Padrões de Código - Clínica Popular

## Convenções Gerais

### Naming
- **Classes**: PascalCase (PatientController, CreatePatientAction)
- **Methods**: camelCase (getPatientById, createNewPatient)
- **Variables**: snake_case ($patient_id, $birth_date)
- **Constants**: UPPER_SNAKE_CASE (MAX_APPOINTMENTS, PATIENT_STATUS)
- **DB Columns**: snake_case (created_at, birth_date, is_active)

### File Structure
- 1 classe por arquivo
- Nome do arquivo = Nome da classe
- Localização do arquivo = namespace da classe
- Use namespaces completos

```php
// ✅ CORRETO
namespace App\Domain\Patient\Models;
class Patient extends Model { }
// Arquivo: app/Domain/Patient/Models/Patient.php

// ❌ ERRADO
namespace App;
class Patient extends Model { }
// Arquivo: app/Patient.php
```

---

## Modelos (Eloquent)

### Structure
```php
<?php

namespace App\Domain\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Patient\Enums\PatientStatus;
use Database\Factories\PatientFactory;

class Patient extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'cpf',
        'birth_date',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => PatientStatus::class,
        'birth_date' => 'date',
    ];

    protected static function newFactory()
    {
        return PatientFactory::new();
    }

    // Relacionamentos
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', PatientStatus::ACTIVE);
    }

    // Mutators (se necessário)
    protected function cpf(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => preg_replace('/\D/', '', $value),
        );
    }
}
```

### Regras
- **Use UUIDs**: Todos os models devem usar `HasUuids`
- **Use HasFactory**: Todos os models devem ter factory
- **Implicit Route Binding**: Use route model binding
- **Eloquent Over Query Builder**: Use métodos Eloquent quando possível
- **Lazy Loading**: Use `with()` para evitar N+1
- **Soft Deletes**: Preferir soft deletes a hard deletes

---

## DTOs (Data Transfer Objects)

### Structure
```php
<?php

namespace App\Domain\Patient\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;

class PatientData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $name,

        #[Required]
        public string $cpf,

        public ?string $phone = null,
    ) {}
}
```

### Regras
- Use Spatie LaravelData para validação
- Defina tipos explícitos
- Use atributos para validação
- Imutáveis por padrão

---

## Actions (Business Logic)

### Structure
```php
<?php

namespace App\Domain\Patient\Actions;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\DataTransferObjects\PatientData;
use App\Domain\Patient\Events\PatientRegistered;
use App\Domain\Patient\Exceptions\DuplicatePatientException;

class CreatePatientAction
{
    public function execute(PatientData $data): Patient
    {
        // 1. Validações de negócio
        if (Patient::where('cpf', $data->cpf)->exists()) {
            throw new DuplicatePatientException();
        }

        // 2. Criar modelo
        $patient = Patient::create([
            'name' => $data->name,
            'cpf' => $data->cpf,
            'phone' => $data->phone,
            'status' => PatientStatus::ACTIVE,
        ]);

        // 3. Disparar eventos
        event(new PatientRegistered($patient));

        // 4. Retornar resultado
        return $patient;
    }
}
```

### Regras
- 1 action por operação (Create, Update, Delete)
- Retornar models, não arrays
- Disparar eventos após sucesso
- Lançar exceções específicas
- Injetar dependências via constructor
- Nunca fazer lógica no controller

---

## Controllers

### Structure
```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Actions\CreatePatientAction;

class PatientController extends Controller
{
    public function __construct(
        private CreatePatientAction $createPatientAction,
    ) {}

    public function index(): JsonResponse
    {
        $patients = Patient::active()->paginate();
        return response()->json(['data' => $patients]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->createPatientAction->execute(
            PatientData::from($request->validated())
        );

        return response()->json([
            'message' => 'Paciente criado com sucesso.',
            'data' => $patient,
        ], 201);
    }
}
```

### Regras
- Controllers são **finos** (thin)
- Delegam lógica para Actions
- Injetam Actions via constructor
- Retornam JSON
- Validação via Form Requests
- Não fazer queries direto (usar Repositories depois)

---

## Requests (Validação)

### Structure
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|regex:/^\d{11}$/',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome é obrigatório',
            'cpf.regex' => 'CPF deve ter 11 dígitos',
        ];
    }
}
```

### Regras
- 1 Request class por rota
- Implementar `authorize()` para permissões
- Mensagens customizadas em português
- Usar `validated()` no controller

---

## Events

### Structure
```php
<?php

namespace App\Domain\Patient\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Patient\Models\Patient;

class PatientRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Patient $patient
    ) {}
}
```

### Regras
- Use Dispatchable + SerializesModels
- Passe apenas dados essenciais
- Listeners podem ser criados depois
- Use `event()` helper para disparar

---

## Exceptions

### Structure
```php
<?php

namespace App\Domain\Patient\Exceptions;

use Exception;

class DuplicatePatientException extends Exception
{
    public function __construct(string $message = 'Paciente já cadastrado com este CPF.')
    {
        parent::__construct($message);
    }
}
```

### Regras
- 1 exception por cenário
- Mensagens customizadas
- Herdar de Exception
- Lançar em Actions, capturar em Controllers/Tests

---

## Tests (Feature)

### Structure
```php
<?php

namespace Tests\Feature\Api\Patient;

use Tests\TestCase;
use App\Domain\Patient\Models\Patient;
use App\Domain\Auth\Models\User;

class PatientControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Patient::truncate();
        User::truncate();
    }

    public function test_can_list_patients(): void
    {
        Patient::factory()->count(3)->create();

        $response = $this->getJson('/api/patients');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/patients', []);

        $response->assertStatus(401);
    }
}
```

### Regras
- Usar `setUp()` para truncar dados
- Testar happy path + edge cases
- 1 assertion por teste (quando possível)
- Nomes descritivos (test_can_..., test_requires_...)
- Usar factories ao invés de mock data
- Sempre testar autenticação

---

## Migrations

### Structure
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->date('birth_date');
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
```

### Regras
- UUIDs como primary key
- Campos nullable explícitos
- Indexes em foreign keys + filtros comuns
- Foreign keys com cascading
- Timestamps automáticos (created_at, updated_at)

---

## Enums

### Structure
```php
<?php

namespace App\Domain\Patient\Enums;

enum PatientStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
        };
    }
}
```

### Regras
- String-backed enums
- Use cases para valores
- Método `label()` para display
- Usar em casts de model

---

## Code Style

- **Indentação**: 4 espaços
- **Line endings**: LF
- **Encoding**: UTF-8
- **Max line length**: 120 caracteres
- **Comments**: Explicar o "por quê", não o "o quê"

```php
// ❌ BAD
$patient = Patient::find($id); // busca paciente

// ✅ GOOD
// Buscar paciente ativo antes de criar agendamento
$patient = Patient::active()->find($id);
```

---

## IDE & Tools

- **Editor**: VSCode com Laravel Extensions
- **Formatter**: Laravel Pint (PSR-12 + Laravel conventions)
- **Static Analysis**: PHPStan (opcional)
- **Git Hooks**: Pre-commit para rodar testes

---

## Checklist para Nova Feature

- [ ] Criar Model com UUID + Factory
- [ ] Criar Enums (se necessário)
- [ ] Criar DTO para transferência de dados
- [ ] Criar Actions para lógica de negócio
- [ ] Criar Events
- [ ] Criar Exceptions
- [ ] Criar Controller com rotas
- [ ] Criar Form Request para validação
- [ ] Escrever Feature Tests
- [ ] Testar manualmente via curl/Postman
- [ ] Documentar na API.md