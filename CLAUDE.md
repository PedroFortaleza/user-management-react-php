# Guia de implementação assistida — Gestão de Usuários

## Missão do projeto

Construir uma aplicação web local, legível e completa para o desafio técnico Full Stack React + PHP. A entrega deve provar o fluxo completo: criar um usuário, persistir com segurança, fazer login com ele, manter a sessão após F5 e gerenciá-lo conforme as permissões.

Prioridade: requisitos funcionais e segurança básica primeiro; diferenciais apenas quando não comprometerem uma solução simples e pronta para demonstrar.

## Restrições inegociáveis

- Backend em PHP 8+ puro: não usar Laravel, Symfony ou banco de dados.
- Persistir em arquivos JSON, com `data/users.json` como fonte de usuários.
- Senhas são sempre `password_hash` no disco e `password_verify` na autenticação. Nunca registrar, retornar ou exibir senha/hash.
- API responde somente JSON, inclusive erros.
- Rotas protegidas exigem `Authorization: Bearer <token>`.
- Frontend é uma SPA React 18+ (Vite), executada localmente com `npm run dev`; API com `php -S`.
- Separar obrigatoriamente Controller, Service, Repository e DTO no backend.
- Não substituir validação de servidor pela validação do cliente: as duas devem existir.

## Arquitetura obrigatória

```text
Request -> Router/Middleware -> Controller -> Service -> Repository -> JSON
                            DTOs -----------^
```

- **Controller:** extrai request, escolhe o DTO, chama o service e transforma resultado/exceção em resposta HTTP. Sem regra de negócio ou acesso a arquivo.
- **DTO:** representa e normaliza dados de entrada (`LoginDTO`, `CreateUserDTO`, `UpdateUserDTO`). Valida formato estrutural; erros por campo são retornados como `422`.
- **Service:** aplica regras de negócio, autenticação, autorização e orquestra repositórios. Nunca depende diretamente de `$_POST` ou `php://input`.
- **Repository:** única camada que lê/escreve JSON. Deve serializar escrita com `flock`, gravar em temporário e usar `rename` atômico. Nunca retorna `password_hash` a controllers.
- **Middleware:** identifica o Bearer token, protege rotas, injeta o usuário autenticado e trata CORS/local quando necessário.

No frontend, agrupar código por responsabilidade: contexto de autenticação, cliente HTTP único, guardas de rotas, recursos de login/usuários e componentes reutilizáveis. Não espalhar `localStorage` ou chamadas `fetch` pelos componentes.

## Contrato funcional mínimo

| Método | Rota | Protegida |
| --- | --- | --- |
| POST | `/api/login` | Não |
| POST | `/api/logout` | Sim |
| GET | `/api/me` | Sim |
| GET | `/api/users` | Sim |
| GET | `/api/users/{id}` | Sim |
| POST | `/api/users` | Sim |
| PUT | `/api/users/{id}` | Sim |
| DELETE | `/api/users/{id}` | Sim |

Respostas de usuário contêm somente `id`, `name`, `email`, `role`, `created_at` e, quando pertinente, `updated_at`. A senha não aparece em nenhuma resposta.

## Regras a preservar

- E-mail único e em formato válido.
- Nome, e-mail, perfil e senha na criação são obrigatórios.
- Senha possui ao menos 8 caracteres.
- Perfis válidos: `admin` e `user`.
- Em edição, senha vazia/ausente mantém o hash atual.
- O usuário autenticado não exclui a própria conta.
- Credencial, token ausente/inválido/expirado retorna `401` sem vazar detalhes.
- JSON ou corpo inválido retorna `400`; recurso inexistente, `404`; erros de campo, `422` no formato consistente `{ "message": "...", "errors": { "campo": ["..."] } }`.
- Usuário criado deve autenticar imediatamente.

## Escolhas planejadas

- Use token opaco criptograficamente aleatório, guardando seu hash e expiração em `data/sessions.json`; não invente JWT manualmente.
- Centralize a sessão no `AuthContext`/hook. Restaure-a ao iniciar pelo token persistido e confirme-a com `/api/me`.
- Diante de `401`, o cliente limpa a sessão e redireciona para `/login`, com feedback compreensível.
- Entregue TypeScript, testes, busca/filtro, RBAC (`admin` gerencia; `user` visualiza), acessibilidade e Docker Compose, nesta ordem apenas após o núcleo estar íntegro.

## Padrões de qualidade

- Prefira funções pequenas, nomes explícitos e dependências injetadas por construtor.
- Use UTC/ISO 8601 com offset nos timestamps e preserve `created_at` em atualizações.
- Não mascarar exceções ou silenciar falhas de escrita. Registre detalhes seguros no servidor e mostre mensagem acionável no cliente.
- Trate carregamento, erro, vazio, sucesso e exclusão confirmada no frontend.
- Todo campo tem `label` associado; modais podem ser fechados por teclado; foco deve ser visível e ir para o local apropriado.
- Não versionar `.env`, `data/users.json`, `data/sessions.json`, `node_modules` ou `vendor`; versionar exemplos e arquivos `.gitkeep` quando necessários.
- Atualize README e testes quando uma decisão pública, rota ou comportamento mudar.

## Processo para cada alteração

1. Leia a fase correspondente em `ROADMAP.md` e os arquivos relacionados antes de editar.
2. Faça a menor alteração completa que atenda ao requisito; não refatore partes não relacionadas.
3. Verifique manualmente o fluxo afetado e execute lint, testes e build aplicáveis.
4. Não declare algo concluído se houver erro de console, resposta não tratada ou dado sensível exposto.
5. Antes de commitar, revise o diff e use Conventional Commits em português ou inglês (`feat:`, `fix:`, `test:`, `docs:`, `chore:`).

## Definition of done

Uma fase só está pronta quando seu código está legível, o caso feliz e as falhas previstas funcionam, os testes disponíveis passam, não há segredos/dados gerados no diff, o README continua verdadeiro e o commit representa uma unidade compreensível.
