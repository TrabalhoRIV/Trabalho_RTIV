# Integra / NIR — Backend Laravel
**Módulo de Consultoria Ambulatorial | TakeOff RTIV**

---

## Pré-requisitos

| Ferramenta | Versão mínima |
|------------|--------------|
| PHP | 8.3+ |
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

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Edite o `.env` e preencha:

- `DB_*` → credenciais do PostgreSQL;
- `AGHU_BASE_URL` e `AGHU_TOKEN` → fornecidos pela equipe de TI do hospital;
- `AI_API_KEY` → chave do provedor de IA (Gemini ou OpenAI).

### 3. Banco de Dados

*Pendente de configuração pela equipe responsável.*

Quando as migrations estiverem prontas e o banco configurado:

```bash
php artisan migrate
```

### 4. Rodar o servidor

```bash
php artisan serve
```

Disponível em: [http://localhost:8000](http://localhost:8000)

Documentação dos endpoints: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

---

## Estrutura do Projeto

```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php       ← login / logout
│   │   │   ├── IA/
│   │   │   │   └── IAController.php         ← endpoints de IA
│   │   │   ├── PacienteController.php       ← busca de pacientes
│   │   │   ├── SolicitacaoController.php    ← solicitações
│   │   │   ├── ParecerController.php        ← emissão de pareceres
│   │   │   └── AgendamentoController.php    ← agendamentos
│   │   └── Requests/
│   │       └── StoreSolicitacaoRequest.php  ← validação de solicitações
│   ├── Services/
│   │   └── AghuService.php                  ← integração com o AGHU
│   └── OpenApi.php                          ← documentação da API
├── config/
│   ├── cors.php                             ← CORS para o Angular
│   ├── services.php                         ← serviços externos
│   └── l5-swagger.php                       ← configuração da documentação
├── routes/
│   └── api.php                              ← rotas da API
├── .env.example                             ← variáveis de ambiente
└── composer.json                            ← dependências PHP
```

---

## Endpoints Disponíveis (Escopo Equipe 1)

| Método | Endpoint | Descrição | HU |
|--------|----------|-----------|-----|
| `POST` | `/api/auth/login` | Autenticar usuário | — |
| `GET` | `/api/auth/me` | Dados do usuário logado | HU01 |
| `POST` | `/api/auth/logout` | Encerrar sessão | — |
| `GET` | `/api/pacientes/{prontuario}` | Buscar paciente no AGHU | HU01 |
| `GET` | `/api/solicitacoes` | Listar solicitações | HU02 |
| `POST` | `/api/solicitacoes` | Criar solicitação | HU02 |
| `GET` | `/api/solicitacoes/{solicitaco}` | Consultar solicitação | HU02 |
| `PUT` | `/api/solicitacoes/{solicitaco}` | Atualizar solicitação | HU02 |
| `PATCH` | `/api/solicitacoes/{solicitaco}` | Atualizar parcialmente | HU02 |
| `POST` | `/api/solicitacoes/{solicitacao}/devolver` | Devolver solicitação | — |
| `POST` | `/api/pareceres` | Registrar parecer | HU07 |
| `POST` | `/api/agendamentos` | Criar agendamento | — |
| `POST` | `/api/ia/descricao` | Auxiliar descrição clínica | HU03 |
| `POST` | `/api/ia/resumo` | Gerar resumo | HU05 |
| `POST` | `/api/ia/prioridade` | Sugerir prioridade | HU06 |
| `POST` | `/api/ia/parecer` | Auxiliar elaboração de parecer | HU07 |

> Todos os endpoints, exceto login, exigem autenticação via Bearer Token.

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
Authorization: Bearer {token}
```

### Criar solicitação

```http
POST /api/solicitacoes
Authorization: Bearer {token}
Content-Type: application/json

{
  "id_paciente": 1,
  "especialidade": "Endocrinologia adulto",
  "descricao_clinica": "Paciente com DM tipo 2, sem controle glicêmico..."
}
```



