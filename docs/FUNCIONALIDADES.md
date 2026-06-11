# 📋 Funcionalidades - Clínica Popular

## Visão Geral
Sistema de agendamento online para clínicas populares com integração WhatsApp, gestão de preços e fila de espera inteligente.

---

## 🔐 Acesso e Autenticação
- Login para: Recepcionistas, Médicos e Administradores
- Pacientes **NÃO** têm login - contato exclusivamente via WhatsApp, telefone ou presencialmente
- Logout seguro
- Autenticação via Sanctum (tokens API)

---

## 👥 Gestão de Pacientes
- Pacientes podem fazer **autocadastro via WhatsApp** (nome, CPF, telefone, data de nascimento)
- Recepcionista também pode cadastrar pacientes (via recepção ou telefone)
- Validação automática de CPF para evitar duplicação
- Histórico completo de consultas realizadas (pode ser enviado ao paciente via WhatsApp)
- Poder ativar/desativar pacientes no sistema
- Buscar pacientes rapidamente
- Status: ATIVO, INATIVO

---

## 👨‍⚕️ Gestão de Médicos
- Cadastro de médicos com CRM (número de registro)
- **Cada médico pode estar associado a VÁRIAS especialidades**
- Guardar contato (email e telefone)
- Adicionar biografia do médico
- Status: ATIVO, INATIVO, DE_LICENÇA
- Visualizar agenda de cada médico

---

## 🏥 Especialidades Médicas
- Cadastro de especialidades (Cardiologia, Oftalmologia, etc)
- Descrição de cada especialidade
- Ativar/desativar especialidades
- Filtrar médicos por especialidade
- Status: ATIVO, INATIVO

---

## 💰 Gestão de Preços
Administrador pode configurar preços de três formas:

### 1. Preço por Especialidade
- Definir valor fixo para cada especialidade
- Ex: Cardiologia = R$ 150,00 por consulta
- Ex: Oftalmologia = R$ 120,00 por consulta

### 2. Preço por Médico (sobrescreve especialidade)
- Definir valor específico para um médico
- Ex: Dr. João (Cardiologia) = R$ 200,00
- Ex: Dr. Maria (Oftalmologia) = R$ 100,00

### 3. Preço por Duração (hora/consulta)
- Cobrar por duração da consulta
- Ex: 30 minutos = R$ 80,00
- Ex: 1 hora = R$ 150,00

### Seleção de Preço (nesta ordem):
1. Preço específico do médico (se configurado)
2. Preço da especialidade (se não houver preço do médico)
3. Preço padrão da clínica (fallback)

---

## 📅 Sistema de Agendamentos
- Recepcionista agenda consultas (via recepção, telefone ou WhatsApp)
- Escolher: Médico, Especialidade, Data e Horário
- **Preço é calculado automaticamente** baseado na configuração
- Confirmação automática do agendamento
- Visualizar agendamentos (recepção e médicos)
- Cancelar agendamentos via WhatsApp (paciente confirma)
- Histórico completo de agendamentos
- Status: AGENDADO, CONFIRMADO, COMPLETADO, CANCELADO

---

## 🕐 Gerenciamento de Turnos
- Definir turnos (Manhã, Tarde, Noite)
- Controlar horários de funcionamento
- Limitar quantidade de agendamentos por turno
- Visualizar disponibilidade em tempo real
- Status: ATIVO, INATIVO

---

## ⏳ Sistema de Interesse + Fila de Espera

### Fluxo Completo:
1. Paciente registra **interesse via WhatsApp** em uma especialidade (sem data/horário ainda)
2. Sistema armazena: data de chegada do interesse, especialidade, range horário preferido
3. Contagem automática de interessados por especialidade
4. Quando atinge **número mínimo de interessados** (ex: 20 pessoas) → clínica abre agenda
5. Sistema **notifica todos os interessados** que abriu nova agenda
   - Informação: *"Cardiologia abriu agenda para 22/07/2026 das 08h às 12h"*
6. Pacientes chegam na clínica ou ligam → recepção marca **por ORDEM DE CHEGADA**
   - Quem chegar primeiro pega primeiro horário disponível
   - Quem chegar depois pega próximos horários
7. Se houver vaga imediatamente → marca direto sem esperar atingir número mínimo
8. Paciente pode desistir do interesse a qualquer momento

### Status da Fila:
- AGUARDANDO (waiting)
- NOTIFICADO (notified)
- CANCELADO (cancelled)

---

## 💳 Pagamentos
- Receber pagamentos via **PIX**
- Receber pagamentos via **Cartão de Crédito**
- Receber pagamentos via **Cartão de Débito**
- Valor da consulta é **calculado automaticamente**
- Gerar comprovantes digitais
- Rastrear pagamentos recebidos e pendentes

---

## 📱 WhatsApp Integration - Fluxo Completo

### Autocadastro e Interesse:
- Paciente faz autocadastro via WhatsApp
- Paciente seleciona especialidade desejada
- Sistema retorna:
  - ✅ Se há **agenda aberta** → mostra datas/horários e valor da consulta
  - ⏳ Se **não há agenda** → oferece opção de registrar interesse em fila

### Notificações Automáticas:
- Confirmação de agendamento (com valor da consulta)
- Lembrete 24h antes da consulta
- Notificação de abertura de agenda quando novas datas são liberadas
- Histórico de consultas pode ser solicitado e enviado (PDF ou texto)

### Ações do Paciente:
- Cancelar agendamento via WhatsApp
- Registrar interesse em especialidades
- Solicitar histórico de consultas realizadas

### Bot WhatsApp:
- Responde: autocadastro, registro de interesse, confirmações, lembretes, cancelamentos, histórico
- **NÃO responde** dúvidas genéricas (direciona para recepção)

---

## 📊 Painéis e Dashboards

### Painel da Recepção
- Ver todos os agendamentos do dia
- Fazer check-in de pacientes que chegaram
- Registrar pagamentos (com valor calculado automaticamente)
- Ver lista de interessados por especialidade e contagem
- Abrir nova agenda manualmente
- Agendar pacientes **por ordem de chegada**
- Visualizar próximas consultas
- Histórico de agendamentos do paciente

### Painel do Médico
- Ver próximas consultas
- Histórico completo do paciente antes de atender
- Registrar observações da consulta

### Painel do Gestor/Administrador

**Gestão de Preços:**
- Configurar preço por especialidade
- Configurar preço por médico
- Configurar preço por duração
- Visualizar histórico de alterações

**Relatórios Financeiros:**
- Receita total e por especialidade
- Receita por médico
- Receita por período (dia/mês/ano)
- Pagamentos recebidos vs pendentes

**Relatórios Operacionais:**
- Taxa de ocupação por médico/especialidade
- Padrões de agendamento
- Relatórios mensais automáticos

**Gestão de Usuários:**
- Criar/editar/desativar recepcionistas e médicos
- Configurar número mínimo de interessados para abrir agenda (por especialidade)

---

## 📑 Documentos Fiscais (Futuro)
- Gerar recibos de serviço (NFS-e)
- Integração automática com prefeitura
- Cumprir obrigações fiscais da clínica

---

## 🎯 Funcionalidades Técnicas (Transparentes ao Usuário)
- API moderna para integração futura com apps
- Banco de dados seguro e confiável
- Backups automáticos
- Acesso via web (desktop) e mobile (recepção)
- Hospedagem segura

---

## 📅 Cronograma Planejado

- **Julho 2026**: MVP básico pronto (agendamentos, especialidades, médicos, sistema de interesse, autenticação, gestão de preços)
- **Agosto 2026**: Sala de espera digital com painel TV, check-in eletrônico
- **Setembro 2026**: Integração com WhatsApp
- **Outubro 2026**: Pagamentos digitais (PIX, cartão)
- **Novembro 2026**: Recibos fiscais (NFS-e) e relatórios financeiros completos