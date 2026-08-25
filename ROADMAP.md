# Roadmap de implementação

O desenvolvimento acontece na branch `develop`; a `main` permanece estável. Cada fase pode ter vários commits pequenos e coerentes, conforme a implementação evoluir. Não misture funcionalidades de fases diferentes no mesmo commit.

Todos os commits seguem Conventional Commits (por exemplo, `feat:`, `fix:`, `test:`, `docs:` e `chore:`). Antes de cada commit, execute as validações aplicáveis à alteração e revise o diff.

O merge de `develop` para `main` não é automático e só deve ser realizado quando solicitado pelo responsável pelo projeto.

## Fase 1 — Fundação e contrato

**Objetivo:** preparar a estrutura, as convenções e uma API executável.

**Status:** concluída.

- [x] Criar `backend/` e `frontend/` com `.gitignore`, `.env.example` e arquivos de configuração mínimos.
- [x] Implementar ponto de entrada PHP, roteador e respostas JSON padronizadas.
- [x] Criar a estrutura `Controller`, `Service`, `Repository`, `DTO`, middleware e exceções/validador compartilhados.
- [x] Iniciar React + Vite + TypeScript, rotas `/login` e `/users` e base visual responsiva.
- [x] Documentar os comandos reais de execução no README.

**Validação:** API retorna JSON em rota inexistente; frontend compila e exibe a rota de login.

**Exemplos de commits possíveis:** `chore: estruturar backend e frontend`; `feat: adicionar roteamento inicial da API`; `feat: iniciar rotas do frontend`

## Fase 2 — Persistência, seed e autenticação

**Objetivo:** entregar login seguro e sessão persistente.

**Status:** concluída.

- [x] Implementar repositórios JSON com bloqueio, escrita temporária e `rename` atômico.
- [x] Criar `seed.php`, administrador inicial e dados ignorados pelo Git.
- [x] Implementar `POST /api/login`, `POST /api/logout` e `GET /api/me` com token opaco, expiração e hashes de senha.
- [x] Implementar `AuthContext`, cliente HTTP centralizado, restauração de sessão e proteção/redirecionamento de rotas.
- [x] Tratar credenciais inválidas, token expirado e `401` na interface.

**Validação:** login com admin funciona; F5 mantém a sessão; logout e token inválido levam ao login; senha/hash não aparecem nas respostas.

**Exemplos de commits possíveis:** `feat: adicionar persistência JSON segura`; `feat: implementar autenticação por token`; `feat: proteger rotas do frontend`

## Fase 3 — CRUD completo e regras de negócio

**Objetivo:** tornar a gestão de usuários funcional de ponta a ponta.

**Status:** concluída.

- [x] Implementar `GET /api/users`, `GET /api/users/{id}`, `POST`, `PUT` e `DELETE /api/users/{id}`.
- [x] Aplicar DTOs e validação de nome, e-mail, senha, perfil e unicidade na API; retornar `400`, `404` e `422` de modo consistente.
- [x] Construir listagem, formulário de criação/edição e confirmação de exclusão.
- [x] Validar no frontend antes do envio e apresentar feedback de sucesso/falha, carregamento, lista vazia e erros por campo.
- [x] Impedir exclusão da própria conta e confirmar que um usuário recém-criado entra imediatamente.

**Validação:** executar manualmente o fluxo criar → logout → login com novo usuário → editar → excluir; testar e-mail duplicado e autoexclusão.

**Exemplos de commits possíveis:** `feat: criar endpoints de usuários`; `feat: adicionar formulário de usuários`; `fix: validar duplicidade de e-mail`

## Fase 4 — Diferenciais e qualidade

**Objetivo:** elevar confiabilidade e experiência sem ampliar a complexidade do núcleo.

- Aplicar RBAC: `admin` gerencia; `user` apenas visualiza o permitido, com proteção no servidor e interface.
- Adicionar busca/filtro na listagem.
- Revisar acessibilidade: labels, foco visível, teclado, mensagens anunciáveis e modal de confirmação.
- Adicionar testes de services/repositórios e dos fluxos principais de autenticação e usuários.
- Configurar lint, build e Docker Compose opcional; documentar os resultados e limitações reais.

**Validação:** testes, lint e build passam; `user` não consegue chamar ações administrativas diretamente na API; interface é navegável por teclado.

**Exemplos de commits possíveis:** `feat: aplicar permissões por perfil`; `test: cobrir regras de usuários`; `feat: adicionar filtros na listagem`

## Fase 5 — Revisão de entrega

**Objetivo:** preparar uma demonstração confiável.

**Status:** concluída.

- [x] Executar o projeto do zero seguindo apenas o README.
- [x] Revisar contrato HTTP, códigos de status, mensagens, ausência de dados sensíveis e dados versionados indevidamente.
- [x] Conferir responsividade, console do navegador e logs de erro.
- [x] Atualizar README com decisões finais, comandos, credenciais, cobertura/checagens e limitações conhecidas.

**Validação:** demonstrar login, F5, CRUD, validações, permissões e logout sem erro no console.

**Exemplos de commits possíveis:** `docs: atualizar instruções de execução`; `test: validar fluxo completo de entrega`; `chore: revisar arquivos da entrega`
