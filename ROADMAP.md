# Roadmap de implementação

Cada fase termina em um commit na `main`. Não misture fases: isso produz um histórico curto, claro e fácil de defender na entrevista.

## Fase 1 — Fundação e contrato

**Objetivo:** preparar a estrutura, as convenções e uma API executável.

- Criar `backend/` e `frontend/` com `.gitignore`, `.env.example` e arquivos de configuração mínimos.
- Implementar ponto de entrada PHP, roteador e respostas JSON padronizadas.
- Criar a estrutura `Controller`, `Service`, `Repository`, `DTO`, middleware e exceções/validador compartilhados.
- Iniciar React + Vite + TypeScript, rotas `/login` e `/users` e base visual responsiva.
- Documentar os comandos reais de execução no README.

**Validação:** API retorna JSON em rota inexistente; frontend compila e exibe a rota de login.

**Commit:** `chore: estrutura inicial da aplicação`

## Fase 2 — Persistência, seed e autenticação

**Objetivo:** entregar login seguro e sessão persistente.

- Implementar repositórios JSON com bloqueio, escrita temporária e `rename` atômico.
- Criar `seed.php`, administrador inicial e dados ignorados pelo Git.
- Implementar `POST /api/login`, `POST /api/logout` e `GET /api/me` com token opaco, expiração e hashes de senha.
- Implementar `AuthContext`, cliente HTTP centralizado, restauração de sessão e proteção/redirecionamento de rotas.
- Tratar credenciais inválidas, token expirado e `401` na interface.

**Validação:** login com admin funciona; F5 mantém a sessão; logout e token inválido levam ao login; senha/hash não aparecem nas respostas.

**Commit:** `feat: autenticação e sessão persistente`

## Fase 3 — CRUD completo e regras de negócio

**Objetivo:** tornar a gestão de usuários funcional de ponta a ponta.

- Implementar `GET /api/users`, `GET /api/users/{id}`, `POST`, `PUT` e `DELETE /api/users/{id}`.
- Aplicar DTOs e validação de nome, e-mail, senha, perfil e unicidade na API; retornar `400`, `404` e `422` de modo consistente.
- Construir listagem, formulário de criação/edição e confirmação de exclusão.
- Validar no frontend antes do envio e apresentar feedback de sucesso/falha, carregamento, lista vazia e erros por campo.
- Impedir exclusão da própria conta e confirmar que um usuário recém-criado entra imediatamente.

**Validação:** executar manualmente o fluxo criar → logout → login com novo usuário → editar → excluir; testar e-mail duplicado e autoexclusão.

**Commit:** `feat: CRUD de usuários com validações`

## Fase 4 — Diferenciais e qualidade

**Objetivo:** elevar confiabilidade e experiência sem ampliar a complexidade do núcleo.

- Aplicar RBAC: `admin` gerencia; `user` apenas visualiza o permitido, com proteção no servidor e interface.
- Adicionar busca/filtro na listagem.
- Revisar acessibilidade: labels, foco visível, teclado, mensagens anunciáveis e modal de confirmação.
- Adicionar testes de services/repositórios e dos fluxos principais de autenticação e usuários.
- Configurar lint, build e Docker Compose opcional; documentar os resultados e limitações reais.

**Validação:** testes, lint e build passam; `user` não consegue chamar ações administrativas diretamente na API; interface é navegável por teclado.

**Commit:** `feat: diferenciais de qualidade e permissões`

## Fase 5 — Revisão de entrega

**Objetivo:** preparar uma demonstração confiável.

- Executar o projeto do zero seguindo apenas o README.
- Revisar contrato HTTP, códigos de status, mensagens, ausência de dados sensíveis e dados versionados indevidamente.
- Conferir responsividade, console do navegador e logs de erro.
- Atualizar README com decisões finais, comandos, credenciais, cobertura/checagens e limitações conhecidas.

**Validação:** demonstrar login, F5, CRUD, validações, permissões e logout sem erro no console.

**Commit:** `docs: finalizar documentação e revisão de entrega`
