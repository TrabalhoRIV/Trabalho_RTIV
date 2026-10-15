# Integra / NIR — Backend Laravel
**Módulo de Consultoria Ambulatorial | TakeOff RTIV**

---

## Pré-requisitos

| Ferramenta | Versão mínima |
|------------|--------------|
| PHP | 8.2+ |
| Composer | 2.x |
| PostgreSQL | 15+ |

---

## Instalação e Configuração

### 1. Instalar dependências
```bash
composer install
```

### 2. Configurar variáveis de ambiente
```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` e preencha:
- `DB_*` → credenciais do PostgreSQL
- `AGHU_BASE_URL` e `AGHU_TOKEN` → fornecidos pela equipe de TI do hospital
- `AI_API_KEY` → chave do provedor de IA (Gemini ou OpenAI)

### 3. Banco de Dados *(pendente — será feito pela Equipe 1)*
```bash
# Quando as migrations estiverem prontas:
php artisan migrate
```

### 4. Rodar o servidor
```bash
php artisan serve
# Disponível em: http://localhost:8000
```

---

## Estrutura do Projeto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php       ← login / logout / me
│   │   │   ├── IA/
│   │   │   │   └── IAController.php         ← endpoints de IA (HU03/05/06/07)
│   │   │   ├── PacienteController.php       ← GET /api/pacientes/{prontuario}
│   │   │   ├── SolicitacaoController.php    ← CRUD + devolver
│   │   │   ├── ParecerController.php        ← POST /api/pareceres
│   │   │   └── AgendamentoController.php    ← POST /api/agendamentos
│   │   └── Requests/
│   │       └── StoreSolicitacaoRequest.php  ← validação HU02
│   └── Services/
│       └── AghuService.php                  ← integração real com o AGHU
├── config/
│   ├── cors.php                             ← CORS para o Angular
│   └── services.php                         ← URLs e tokens externos
├── routes/
│   └── api.php                              ← todas as rotas da API
├── .env.example                             ← variáveis de ambiente (copiar para .env)
└── composer.json                            ← dependências PHP
```

---

## Endpoints Disponíveis (Escopo Equipe 1)

| Método | Endpoint | Descrição | HU |
|--------|----------|-----------|-----|
| `POST` | `/api/auth/login` | Autenticar usuário | — |
| `GET`  | `/api/auth/me` | Dados do usuário logado (nome, CRM) | HU01 |
| `GET`  | `/api/pacientes/{prontuario}` | Buscar paciente no AGHU | HU01 |
| `POST` | `/api/solicitacoes` | Criar solicitação | HU02 |

> Todos os endpoints (exceto login) exigem header: `Authorization: Bearer {token}`

---

## Exemplo de Requisição

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "medico@hospital.gov.br",
  "password": "senha123"
}
```

### Buscar paciente no AGHU
```http
GET /api/pacientes/123456
Authorization: Bearer 1|abc...
```

### Criar solicitação
```http
POST /api/solicitacoes
Authorization: Bearer 1|abc...
Content-Type: application/json

{
  "id_paciente": 1,
  "especialidade": "Endocrinologia adulto",
  "descricao_clinica": "Paciente com DM tipo 2, sem controle glicêmico..."
}
```
