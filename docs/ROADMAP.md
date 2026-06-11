# 📅 Roadmap - Clínica Popular

## Status: MVP (Mínimo Viável)

### ✅ Completado (Junho 2026)

#### Core Domains
- [x] Autenticação (Login/Logout)
- [x] Gestão de Pacientes
- [x] Gestão de Médicos
- [x] Especialidades Médicas
- [x] Turnos/Agendas
- [x] Sistema de Agendamentos
- [x] Fila de Espera (WaitingList)
- [x] Gestão de Preços (por especialidade, médico, duração)

#### API
- [x] Endpoints Auth (register, login, logout, me)
- [x] Endpoints Patients (CRUD)
- [x] Endpoints Specialties (CRUD)
- [x] Endpoints Doctors (CRUD)
- [x] Endpoints Shifts (CRUD)
- [x] Endpoints Appointments (CRUD + confirm + cancel)
- [x] Endpoints WaitingList (CRUD + notify)

#### Tests
- [x] Feature Tests para todos os endpoints
- [x] Validação de CPF com Mod 11
- [x] Proteção de rotas autenticadas
- [x] Constraint UNIQUE em agendamentos

#### Infrastructure
- [x] Docker (PHP + PostgreSQL + Redis)
- [x] Migrations e Seeders
- [x] Factories para testes
- [x] Database schema com UUIDs

---

### 🚧 Em Desenvolvimento (Julho/Agosto 2026)

#### Frontend Básico
- [ ] Painel da Recepção (Livewire + Alpine.js)
- [ ] Painel do Médico (Livewire + Alpine.js)
- [ ] Painel do Administrador (Livewire + Alpine.js)
- [ ] Sala de Espera Digital (Painel TV)

#### Real-time
- [ ] WebSockets via Laravel Reverb
- [ ] Notificações ao vivo

#### Melhorias UX
- [ ] Check-in eletrônico
- [ ] Busca de horários disponíveis
- [ ] Filtros avançados

---

### ⏳ Planejado (Setembro 2026)

#### WhatsApp Integration
- [ ] Autocadastro via WhatsApp
- [ ] Registro de interesse via WhatsApp
- [ ] Confirmações automáticas
- [ ] Lembretes 24h antes
- [ ] Cancelamento via WhatsApp
- [ ] Histórico de consultas
- [ ] Bot inteligente (Evolution API)

#### Payment Gateway
- [ ] Integração PIX (Asaas/Mercado Pago)
- [ ] Integração Cartão de Crédito
- [ ] Integração Débito em Conta
- [ ] Comprovantes digitais
- [ ] Rastreamento de pagamentos

---

### 🎯 Futuro (Outubro/Novembro 2026)

#### Documentos Fiscais
- [ ] Geração de Recibos (NFS-e)
- [ ] Integração com Prefeitura
- [ ] Obrigações Fiscais

#### Relatórios Avançados
- [ ] Dashboard financeiro
- [ ] Relatórios operacionais
- [ ] Exportação em PDF/Excel
- [ ] Gráficos e análises

#### Escalabilidade
- [ ] Queue jobs com Horizon
- [ ] Cache strategy (Redis)
- [ ] CI/CD (GitHub Actions)
- [ ] Monitoring e alertas
- [ ] Backup automático

---

### 🎁 Nice to Have

- [ ] Mobile App (Flutter/React Native)
- [ ] Integração com calendários (Google/Outlook)
- [ ] Sistema de avaliações/feedback
- [ ] Gestão de documentos do paciente
- [ ] Receitas digitais
- [ ] Prontuário eletrônico
- [ ] Teleconsulta (Zoom/Meet integration)
- [ ] Analytics avançado
- [ ] Multi-clínica (SaaS)

---

## 📊 Métricas de Sucesso

- ✅ Todos os endpoints testados
- ✅ Cobertura de testes > 80%
- ✅ Tempo de response < 200ms
- ✅ Database schema normalizado
- ✅ Zero security vulnerabilities
- ⏳ WhatsApp integration ativo
- ⏳ Pagamentos processando

---

## 🔄 Sprint Planning

### Sprint 1 (Julho 2026)
- Painel da Recepção MVP
- Painel do Médico MVP
- Painel Admin Básico

### Sprint 2 (Agosto 2026)
- Sala de Espera Digital
- Check-in eletrônico
- Melhorias UX

### Sprint 3 (Setembro 2026)
- WhatsApp Bot completo
- Payment Gateway integrado
- Testes de load

### Sprint 4 (Outubro 2026)
- NFS-e integration
- Relatórios avançados
- Performance optimization

---

## 🚀 Deploy Strategy

**Staging**: Deploy automático a cada push em `develop`
**Production**: Deploy manual em releases (tags v0.1.0, v0.2.0, etc)

---

## 📞 Support

Dúvidas sobre o roadmap? Abra uma issue no GitHub com o label `roadmap`.