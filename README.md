# Sistema de Gestão de Usuários

Aplicação full stack local para autenticação e gerenciamento de usuários. O projeto combina uma API REST em PHP puro com uma SPA React, usando arquivos JSON como persistência — conforme o desafio técnico.

> Status: Fases 1, 2 e 3 concluídas. A aplicação possui autenticação persistente e CRUD completo de usuários; os diferenciais serão desenvolvidos nas próximas fases descritas em [ROADMAP.md](ROADMAP.md).

## Objetivo

Permitir que um usuário autenticado consulte, cadastre, edite e exclua usuários. O fluxo central é verificável de ponta a ponta: um usuário criado pela interface consegue entrar imediatamente com seu e-mail e senha.

## Tecnologias

| Camada | Escolha | Motivo |
| --- | --- | --- |
| API | PHP 8+ sem framework | Atende ao desafio e deixa o fluxo HTTP explícito. |
| Persistência | Arquivos JSON | Substitui o banco de dados, como solicitado. |
| Frontend | React 18 + Vite + TypeScript | SPA rápida, tipada e simples de executar. |
| HTTP | `fetch` encapsulado em um cliente | Evita dependência desnecessária e centraliza o token/erros. |
| Rotas | React Router | Separa as rotas públicas e protegidas. |
| Testes | PHPUnit e Vitest/Testing Library | Cobrem regras críticas no backend e interface. |

## Estrutura atual

```text
.
├── backend/
│   ├── public/index.php          # ponto de entrada e roteamento HTTP
│   ├── src/
│   │   ├── Controller/           # traduz HTTP em chamadas de serviço
│   │   ├── DTO/                  # dados de entrada validados e tipados
│   │   ├── Repository/           # leitura/gravação segura dos JSONs
│   │   ├── Service/              # regras de negócio e autorização
│   │   ├── Middleware/           # autenticação e tratamento comum
│   │   └── Support/              # respostas, validação e exceções
│   ├── data/                     # users.json e sessions.json locais (não versionados)
│   ├── seed.php                  # cria o administrador inicial
│   └── .env.example
├── frontend/
│   └── src/
│       ├── components/           # componentes reutilizáveis
│       ├── contexts/             # sessão centralizada
│       ├── features/             # login e usuários por domínio
│       ├── routes/               # guardas de rota
│       ├── services/             # cliente HTTP centralizado
│       └── styles/               # estilos globais responsivos
├── CLAUDE.md                     # instruções de desenvolvimento assistido
├── ROADMAP.md                    # fases e commits sugeridos
└── README.md
```

## Pré-requisitos

- PHP 8.0 ou superior, com extensão JSON habilitada;
- Node.js 18 ou superior e npm;
- Git (opcional, para o fluxo de commits);
- Docker e Docker Compose (opcionais, para o diferencial de ambiente conteinerizado).

## Como executar

Os comandos abaixo funcionam até a Fase 3.

1. Prepare as variáveis do backend (opcional, mas recomendado para configurar a origem permitida pelo CORS):

   ```bash
   cd backend
   cp .env.example .env
   ```

   No Windows PowerShell, substitua cada `cp .env.example .env` por `Copy-Item .env.example .env`.

2. Crie o usuário administrador inicial e inicie a API em um terminal:

   ```bash
   cd backend
   php seed.php
   php -S localhost:8000 -t public
   ```

   Verifique a API em `http://localhost:8000/api/health`. A resposta esperada é `{"status":"ok"}`.

3. Em outro terminal, prepare e inicie o frontend:

   ```bash
   cd frontend
   cp .env.example .env
   npm install
   npm run dev
   ```

4. Acesse a URL informada pelo Vite (normalmente `http://localhost:5173`).

### Credenciais iniciais

Após executar o seed, use:

| Campo | Valor |
| --- | --- |
| E-mail | `admin@empresa.com` |
| Senha | `Admin@123` |

Essas credenciais existem apenas para desenvolvimento local. A senha é persistida exclusivamente como hash gerado por `password_hash`.

## Contrato-alvo da API

Todas as respostas são JSON. As rotas documentadas abaixo estão disponíveis. Rotas protegidas exigem `Authorization: Bearer <token>`.

| Método | Rota | Descrição |
| --- | --- | --- |
| POST | `/api/login` | Autentica e retorna token e usuário seguro. |
| POST | `/api/logout` | Invalida a sessão atual. |
| GET | `/api/me` | Retorna o usuário autenticado. |
| GET | `/api/users` | Lista usuários. |
| GET | `/api/users/{id}` | Detalha um usuário. |
| POST | `/api/users` | Cria usuário. |
| PUT | `/api/users/{id}` | Atualiza usuário; senha vazia é ignorada. |
| DELETE | `/api/users/{id}` | Remove usuário, exceto o próprio autenticado. |

Os retornos de erro serão consistentes: `400` para JSON/requisição inválida, `401` para autenticação, `404` para recurso ausente e `422` para erros de validação por campo. Senhas e hashes nunca são serializados pela API.

## Decisões técnicas

- **Arquitetura em camadas:** controllers recebem a requisição, DTOs normalizam entradas, services aplicam regras e repositories isolam a persistência. Isso mantém o código direto de seguir e de testar.
- **Persistência segura:** o repositório escreve em arquivo temporário, mantém um `flock` durante a operação e finaliza com `rename` atômico. Assim, um leitor não recebe um JSON parcialmente escrito e escritas simultâneas são serializadas.
- **Sessão com token opaco:** tokens aleatórios, com expiração configurável, têm somente o hash guardado em `sessions.json`; o navegador mantém o token e os dados seguros do usuário. Logout remove o token do armazenamento local e invalida a sessão no servidor.
- **Autorização por perfil:** `admin` gerencia usuários; `user` pode consultar a lista e o próprio perfil, sem alterar cadastros. A regra é aplicada no servidor, não apenas na interface.
- **Validação duplicada com propósito:** o frontend dá retorno imediato; o backend é a fonte de verdade para formato do e-mail, unicidade, senha mínima de 8 caracteres, perfil permitido e campos obrigatórios.
- **Experiência e acessibilidade:** haverá estados visuais de carregamento, erro e vazio; mensagens de sucesso/falha; confirmação de exclusão; labels associados, foco visível e fluxo por teclado.

## Diferenciais planejados

- Frontend em TypeScript;
- testes automatizados para fluxos e regras importantes;
- busca e filtro na lista;
- autorização por perfil;
- expiração de token e redirecionamento automático após `401`;
- acessibilidade básica verificável;
- Docker Compose opcional para iniciar o ambiente.

## Scripts de qualidade disponíveis

Execute estes comandos antes de commitar alterações no frontend:

```bash
# frontend
npm run lint
npm run build
```

Testes automatizados de frontend e backend serão adicionados na Fase 4.

## Limitações conhecidas

- A persistência em JSON é apropriada ao desafio e ao uso local, mas não substitui um banco de dados em produção.
- Tokens e arquivos de dados não são um provedor de identidade nem um cofre de segredos; são uma implementação local didática.
- A política de permissão detalhada será documentada e testada junto da implementação para evitar divergência entre API e interface.
- As permissões por perfil serão refinadas na Fase 4. Nesta fase, qualquer usuário autenticado pode gerenciar cadastros.

## Histórico de implementação

Os commits devem acompanhar as fases em [ROADMAP.md](ROADMAP.md), mantendo alterações pequenas e revisáveis. Consulte também [CLAUDE.md](CLAUDE.md) para as regras que orientam qualquer trabalho assistido por IA neste repositório.
