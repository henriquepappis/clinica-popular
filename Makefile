.PHONY: help install up down restart logs shell tinker migrate migrate-fresh seed test pint analyze queue horizon mailpit db redis-cli clean optimize npm-dev npm-build backup-db restore-db ps wait-horizon verify-install restart-horizon

help: ## Mostrar ajuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Instalar projeto completo com Horizon
	@echo "🚀 Instalando Clínica Popular..."
	cp .env.example .env
	docker-compose up -d
	@echo "⏳ Aguardando containers iniciarem (15s)..."
	sleep 15
	@echo "📦 Instalando dependências Composer..."
	docker-compose exec -T app composer install
	@echo "🔑 Gerando APP_KEY..."
	docker-compose exec -T app php artisan key:generate
	@echo "⚙️  Instalando Horizon..."
	docker-compose exec -T app composer require laravel/horizon --no-interaction 2>/dev/null || true
	docker-compose exec -T app php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider" --force 2>/dev/null || true
	@echo "📊 Rodando migrations..."
	docker-compose exec -T app php artisan migrate --force
	@echo "🎨 Instalando dependências frontend..."
	docker-compose exec -T app npm install
	@echo "🏗️  Buildando assets..."
	docker-compose exec -T app npm run build
	@echo "⏳ Aguardando Horizon iniciar (10s)..."
	sleep 10
	@echo ""
	@echo "✅ Instalação concluída!"
	@echo ""
	@echo "📍 Endpoints disponíveis:"
	@echo "   App:       http://localhost"
	@echo "   Horizon:   http://localhost/horizon"
	@echo "   Mailpit:   http://localhost:8025"
	@echo ""
	@echo "🚀 Para começar:"
	@echo "   make ps          # Ver status dos containers"
	@echo "   make logs        # Ver logs em tempo real"
	@echo "   make shell       # Entrar no container"
	@echo "   make help        # Ver todos os comandos"

ps: ## Status dos containers
	docker-compose ps

up: ## Subir containers
	docker-compose up -d
	@echo "⏳ Aguardando containers iniciarem (5s)..."
	sleep 5
	@echo "✅ Containers iniciados!"
	@echo ""
	@echo "📍 Status:"
	docker-compose ps
	@echo ""
	@echo "🌐 Acesse:"
	@echo "   App:     http://localhost"
	@echo "   Horizon: http://localhost/horizon"
	@echo "   Mailpit: http://localhost:8025"

down: ## Parar containers
	docker-compose down

restart: ## Reiniciar containers
	docker-compose restart
	@echo "✅ Containers reiniciados!"

restart-horizon: ## Reiniciar Horizon worker
	@echo "🔄 Reiniciando Horizon..."
	docker-compose restart clinica_horizon
	@echo "⏳ Aguardando iniciar (5s)..."
	sleep 5
	@echo "✅ Horizon reiniciado!"
	docker-compose exec -T app php artisan horizon:status

wait-horizon: ## Aguardar Horizon estar pronto
	@echo "⏳ Aguardando Horizon iniciar..."
	@for i in {1..30}; do \
		if docker-compose exec -T app php artisan horizon:status 2>/dev/null | grep -q "running"; then \
			echo "✅ Horizon iniciado!"; \
			break; \
		fi; \
		echo "   Tentativa $$i/30..."; \
		sleep 2; \
	done

verify-install: ## Verificar se tudo está funcionando
	@echo "🔍 Verificando instalação..."
	@echo ""
	@echo "1️⃣  App (Laravel 13):"
	@curl -s http://localhost > /dev/null && echo "   ✅ http://localhost" || echo "   ❌ http://localhost"
	@echo ""
	@echo "2️⃣  Horizon Dashboard:"
	@curl -s http://localhost/horizon > /dev/null && echo "   ✅ http://localhost/horizon" || echo "   ❌ http://localhost/horizon"
	@echo ""
	@echo "3️⃣  Mailpit:"
	@curl -s http://localhost:8025 > /dev/null && echo "   ✅ http://localhost:8025" || echo "   ❌ http://localhost:8025"
	@echo ""
	@echo "4️⃣  Containers:"
	@docker-compose ps --format "table {{.Names}}\t{{.Status}}" | grep -E "app|nginx|postgres|redis|horizon" || true
	@echo ""
	@echo "✅ Verificação concluída!"

logs: ## Ver logs em tempo real
	docker-compose logs -f

shell: ## Entrar no container app
	docker-compose exec app sh

tinker: ## Laravel Tinker
	docker-compose exec app php artisan tinker

migrate: ## Rodar migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Resetar banco (⚠️  CUIDADO - deleta dados!)
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Rodar seeders
	docker-compose exec app php artisan db:seed

test: ## Rodar testes
	docker-compose exec app php artisan test

test-debug: ## Rodar testes com Xdebug
	docker-compose exec -T app docker-php-ext-enable xdebug
	docker-compose restart app
	@echo "✅ Xdebug habilitado! Abra Debug no VSCode (Ctrl+Shift+D)"
	@sleep 3
	docker-compose exec -T app php artisan test
	@echo "Desabilitando Xdebug..."
	docker-compose exec -T app docker-php-ext-disable xdebug
	docker-compose restart app
	@echo "✅ Xdebug desabilitado!"

pint: ## Code style fix (PHP)
	docker-compose exec app ./vendor/bin/pint

analyze: ## Análise estática (PHPStan)
	docker-compose exec app ./vendor/bin/phpstan analyse

queue: ## Monitorar filas em tempo real
	docker-compose exec app php artisan queue:work

horizon: ## Informações do Horizon
	@echo "📊 Status do Horizon:"
	docker-compose exec app php artisan horizon:status
	@echo ""
	@echo "🌐 Acesse o dashboard em: http://localhost/horizon"

mailpit: ## Abrir Mailpit (emails)
	@echo "📧 Mailpit disponível em: http://localhost:8025"

db: ## Conectar ao PostgreSQL
	docker-compose exec postgres psql -U clinica_user -d clinica_popular

db-show: ## Informações do banco
	docker-compose exec app php artisan db:show

redis-cli: ## Conectar ao Redis
	docker-compose exec redis redis-cli

clean: ## Limpar caches (Laravel)
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "✅ Caches limpos!"

optimize: ## Otimizar para produção
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	docker-compose exec app php artisan event:cache
	@echo "✅ Otimizações aplicadas!"

npm-dev: ## NPM desenvolvimento (watch)
	docker-compose exec app npm run dev

npm-build: ## NPM build production
	docker-compose exec app npm run build

backup-db: ## Fazer backup do banco
	docker-compose exec postgres pg_dump -U clinica_user clinica_popular > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup criado!"

restore-db: ## Restaurar banco (usar: make restore-db FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "❌ Erro: Specify file with FILE=backup.sql"; \
		exit 1; \
	fi
	docker-compose exec -T postgres psql -U clinica_user clinica_popular < $(FILE)
	@echo "✅ Banco restaurado de $(FILE)!"