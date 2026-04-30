<?php

namespace App\Service\MarkdownGenerator;

class MarkdownGenerator
{
    public function generateFiles(array $recommendation): array
    {
        $result = $recommendation['result'] ?? $recommendation;
        $stack = $result['stack'] ?? [];
        $libraries = $result['libraries'] ?? [];
        $metadata = $result['metadata'] ?? [];

        return [
            'PROJECT.md' => $this->generateProjectFile($stack, $metadata),
            'ARCHITECTURE.md' => $this->generateArchitectureFile($stack, $metadata),
            'STACK.md' => $this->generateStackFile($stack),
            'CONVENTIONS.md' => $this->generateConventionsFile($stack),
            'SETUP.md' => $this->generateSetupFile($stack),
            'LIBRARIES.md' => $this->generateLibrariesFile($libraries),
        ];
    }

    private function generateArchitectureFile(array $stack, array $metadata): string
    {
        $roles       = array_column($stack, 'role');
        $categories  = array_column($stack, 'category');
        $languages   = array_unique(array_filter(array_map(fn($t) => strtolower($t['language'] ?? ''), $stack)));
        $primaryLang = $languages[0] ?? 'javascript';

        $hasFullstack = in_array('fullstack', $roles);
        $hasFrontend  = $hasFullstack || in_array('frontend', $roles);
        $hasBackend   = $hasFullstack || in_array('backend', $roles);
        $hasDatabase  = in_array('database', $roles) || in_array('database', $categories);

        $isTypeScript = in_array('typescript', $languages) || in_array('javascript', $languages);
        $isPython     = in_array('python', $languages);
        $isPHP        = in_array('php', $languages);
        $isGo         = in_array('go', $languages);

        $primaryTech = $stack[0] ?? [];
        $primaryId   = $primaryTech['id'] ?? '';

        $md  = "# ARCHITECTURE.md\n\n";
        $md .= "> Auto-generated architecture guide for your recommended stack. ";
        $md .= "Apply every rule listed here from day one — retrofitting is expensive.\n\n";

        // ── 1. CLEAN ARCHITECTURE ──────────────────────────────────────────
        $md .= "---\n\n## 1. Clean Architecture\n\n";
        $md .= "Organise code in **four concentric layers**. ";
        $md .= "Each layer can only depend on the one directly inside it — never outward.\n\n";
        $md .= "```\n";
        $md .= "┌─────────────────────────────────────────┐\n";
        $md .= "│  Interface (HTTP, CLI, WebSocket)        │  ← outermost: controllers, routes, DTOs\n";
        $md .= "│  ┌───────────────────────────────────┐  │\n";
        $md .= "│  │  Infrastructure (DB, Cache, Email) │  │  ← adapters: repos, ORM, 3rd-party\n";
        $md .= "│  │  ┌─────────────────────────────┐  │  │\n";
        $md .= "│  │  │  Application (Use Cases)     │  │  │  ← orchestration: services, commands\n";
        $md .= "│  │  │  ┌───────────────────────┐  │  │  │\n";
        $md .= "│  │  │  │  Domain (Core)         │  │  │  │  ← innermost: entities, value objects\n";
        $md .= "│  │  │  └───────────────────────┘  │  │  │\n";
        $md .= "│  │  └─────────────────────────────┘  │  │\n";
        $md .= "│  └───────────────────────────────────┘  │\n";
        $md .= "└─────────────────────────────────────────┘\n";
        $md .= "```\n\n";

        $md .= "| Layer | Responsibility | May import |\n";
        $md .= "|-------|----------------|------------|\n";
        $md .= "| **Domain** | Business rules, entities, value objects, domain events | nothing external |\n";
        $md .= "| **Application** | Use-case logic, command/query handlers | Domain only |\n";
        $md .= "| **Infrastructure** | DB, cache, email, file storage, 3rd-party SDKs | Application + Domain |\n";
        $md .= "| **Interface** | HTTP controllers, GraphQL resolvers, CLI commands | Application (DTOs) |\n\n";

        $md .= "> **Rule:** if you find yourself importing a DB model inside a domain entity, you broke the boundary.\n\n";

        // ── 2. SOLID PRINCIPLES ───────────────────────────────────────────
        $md .= "---\n\n## 2. SOLID Principles\n\n";

        if ($isTypeScript) {
            $md .= "### S — Single Responsibility\n";
            $md .= "One class = one reason to change.\n\n";
            $md .= "```typescript\n";
            $md .= "// ✗ violates SRP — handles logic AND sends email\n";
            $md .= "class UserService {\n";
            $md .= "  async register(dto: RegisterDto) {\n";
            $md .= "    const user = await this.repo.create(dto)\n";
            $md .= "    await sendMail(user.email, 'Welcome!')  // ← unrelated concern\n";
            $md .= "    return user\n";
            $md .= "  }\n";
            $md .= "}\n\n";
            $md .= "// ✓ correct — delegates notification to a dedicated service\n";
            $md .= "class UserService {\n";
            $md .= "  constructor(private repo: UserRepository, private mailer: Mailer) {}\n";
            $md .= "  async register(dto: RegisterDto) {\n";
            $md .= "    const user = await this.repo.create(dto)\n";
            $md .= "    await this.mailer.sendWelcome(user.email)\n";
            $md .= "    return user\n";
            $md .= "  }\n";
            $md .= "}\n";
            $md .= "```\n\n";

            $md .= "### O — Open / Closed\n";
            $md .= "Open for extension, closed for modification. Add new behaviour through new classes, not `if/else` chains.\n\n";
            $md .= "```typescript\n";
            $md .= "// ✓ add a new payment provider without touching existing code\n";
            $md .= "interface PaymentProvider { charge(amount: number): Promise<void> }\n";
            $md .= "class StripeProvider implements PaymentProvider { ... }\n";
            $md .= "class PayPalProvider implements PaymentProvider { ... }\n";
            $md .= "```\n\n";

            $md .= "### L — Liskov Substitution\n";
            $md .= "Any subclass must be usable wherever the parent is expected without breaking the program.\n\n";
            $md .= "```typescript\n";
            $md .= "// ✗ violates LSP — throws where parent returns a value\n";
            $md .= "class ReadOnlyRepository extends UserRepository {\n";
            $md .= "  save(): never { throw new Error('Read-only') }\n";
            $md .= "}\n";
            $md .= "// ✓ model read-only access with a separate interface instead\n";
            $md .= "```\n\n";

            $md .= "### I — Interface Segregation\n";
            $md .= "Many small, focused interfaces beat one fat interface.\n\n";
            $md .= "```typescript\n";
            $md .= "// ✗ forces implementations to provide methods they don't need\n";
            $md .= "interface Repository { findAll(): Promise<User[]>; save(u: User): Promise<void>; delete(id: string): Promise<void> }\n\n";
            $md .= "// ✓ split by capability\n";
            $md .= "interface Readable<T> { findById(id: string): Promise<T | null>; findAll(): Promise<T[]> }\n";
            $md .= "interface Writable<T> { save(entity: T): Promise<T>; delete(id: string): Promise<void> }\n";
            $md .= "```\n\n";

            $md .= "### D — Dependency Inversion\n";
            $md .= "High-level modules depend on abstractions, not concrete implementations.\n\n";
            $md .= "```typescript\n";
            $md .= "// ✓ domain service depends on the interface, not on Prisma directly\n";
            $md .= "class OrderService {\n";
            $md .= "  constructor(private orders: OrderRepository) {}  // interface\n";
            $md .= "}\n";
            $md .= "class PrismaOrderRepository implements OrderRepository { ... }  // concrete\n";
            $md .= "```\n\n";
        } elseif ($isPython) {
            $md .= "### S — Single Responsibility\n";
            $md .= "```python\n";
            $md .= "# ✓ separate concerns into distinct classes\n";
            $md .= "class UserRepository:\n";
            $md .= "    def save(self, user: User) -> User: ...\n\n";
            $md .= "class UserMailer:\n";
            $md .= "    def send_welcome(self, email: str) -> None: ...\n\n";
            $md .= "class RegisterUserUseCase:\n";
            $md .= "    def __init__(self, repo: UserRepository, mailer: UserMailer): ...\n";
            $md .= "    def execute(self, dto: RegisterDto) -> User: ...\n";
            $md .= "```\n\n";

            $md .= "### O — Open / Closed\n";
            $md .= "```python\n";
            $md .= "from abc import ABC, abstractmethod\n\n";
            $md .= "class PaymentProvider(ABC):\n";
            $md .= "    @abstractmethod\n";
            $md .= "    def charge(self, amount: int) -> None: ...\n\n";
            $md .= "class StripeProvider(PaymentProvider): ...\n";
            $md .= "class PayPalProvider(PaymentProvider): ...\n";
            $md .= "```\n\n";

            $md .= "### D — Dependency Inversion\n";
            $md .= "```python\n";
            $md .= "# ✓ inject dependencies through __init__, never instantiate inside\n";
            $md .= "class OrderService:\n";
            $md .= "    def __init__(self, repo: OrderRepository, events: EventBus): ...\n";
            $md .= "```\n\n";
        } elseif ($isPHP) {
            $md .= "### S — Single Responsibility\n";
            $md .= "```php\n";
            $md .= "// ✓ one class per responsibility\n";
            $md .= "class UserRepository { public function save(User \$u): User { ... } }\n";
            $md .= "class Mailer          { public function sendWelcome(string \$email): void { ... } }\n";
            $md .= "class RegisterUserHandler {\n";
            $md .= "    public function __construct(private UserRepository \$repo, private Mailer \$mailer) {}\n";
            $md .= "    public function handle(RegisterCommand \$cmd): User { ... }\n";
            $md .= "}\n";
            $md .= "```\n\n";

            $md .= "### D — Dependency Inversion\n";
            $md .= "```php\n";
            $md .= "// ✓ depend on interfaces, not concrete classes\n";
            $md .= "interface UserRepositoryInterface { public function findById(string \$id): ?User; }\n";
            $md .= "class DoctrineUserRepository implements UserRepositoryInterface { ... }\n";
            $md .= "```\n\n";
        } else {
            $md .= "Apply the five SOLID rules regardless of language:\n\n";
            $md .= "| Principle | Rule |\n";
            $md .= "|-----------|------|\n";
            $md .= "| **S**ingle Responsibility | One class = one reason to change |\n";
            $md .= "| **O**pen / Closed | Extend behaviour via new code, not by editing existing |\n";
            $md .= "| **L**iskov Substitution | Subtypes must be drop-in replacements for their parents |\n";
            $md .= "| **I**nterface Segregation | Many small interfaces > one large one |\n";
            $md .= "| **D**ependency Inversion | Depend on abstractions, not on concrete implementations |\n\n";
        }

        // ── 3. FOLDER STRUCTURE ───────────────────────────────────────────
        $md .= "---\n\n## 3. Folder Structure\n\n";

        if ($hasFrontend && !$hasFullstack) {
            $md .= "### Frontend\n\n";
            $md .= $this->frontendFolderTree($primaryId, $isTypeScript);
            $md .= "\n";
        }

        if ($hasFullstack) {
            $md .= "### Fullstack — " . ($primaryTech['name'] ?? 'App') . "\n\n";
            $md .= $this->fullstackFolderTree($primaryId, $isTypeScript);
            $md .= "\n";
        }

        if ($hasBackend && !$hasFullstack) {
            $md .= "### Backend\n\n";
            $md .= $this->backendFolderTree($primaryLang, $isTypeScript, $isPython, $isPHP, $isGo);
            $md .= "\n";
        }

        // ── 4. NAMING CONVENTIONS ─────────────────────────────────────────
        $md .= "---\n\n## 4. Naming Conventions\n\n";

        if ($isTypeScript) {
            $md .= "| Artefact | Convention | Example |\n";
            $md .= "|----------|------------|---------|\n";
            $md .= "| React component file | `PascalCase.tsx` | `UserCard.tsx` |\n";
            $md .= "| Hook | `use` + `PascalCase` | `useAuthState.ts` |\n";
            $md .= "| Service / class | `PascalCase` | `OrderService.ts` |\n";
            $md .= "| Interface / type | `PascalCase` | `UserRepository.ts` |\n";
            $md .= "| Utility function | `camelCase` | `formatCurrency.ts` |\n";
            $md .= "| Constant | `UPPER_SNAKE_CASE` | `MAX_RETRIES` |\n";
            $md .= "| API route | `kebab-case` | `/api/user-profiles` |\n";
            $md .= "| DB table / column | `snake_case` | `user_profiles.created_at` |\n";
            $md .= "| Env variable | `UPPER_SNAKE_CASE` | `DATABASE_URL` |\n\n";
        } elseif ($isPython) {
            $md .= "| Artefact | Convention | Example |\n";
            $md .= "|----------|------------|---------|\n";
            $md .= "| Module / file | `snake_case.py` | `user_service.py` |\n";
            $md .= "| Class | `PascalCase` | `UserService` |\n";
            $md .= "| Function / method | `snake_case` | `get_user_by_id` |\n";
            $md .= "| Constant | `UPPER_SNAKE_CASE` | `MAX_RETRIES` |\n";
            $md .= "| API route | `kebab-case` | `/api/user-profiles` |\n";
            $md .= "| DB table / column | `snake_case` | `user_profiles.created_at` |\n\n";
        } elseif ($isPHP) {
            $md .= "| Artefact | Convention | Example |\n";
            $md .= "|----------|------------|---------|\n";
            $md .= "| Class / interface | `PascalCase` | `UserService` |\n";
            $md .= "| Method | `camelCase` | `findByEmail` |\n";
            $md .= "| Variable | `camelCase` | `\$userId` |\n";
            $md .= "| Constant | `UPPER_SNAKE_CASE` | `MAX_RETRIES` |\n";
            $md .= "| API route | `kebab-case` | `/api/user-profiles` |\n";
            $md .= "| DB table / column | `snake_case` | `user_profiles.created_at` |\n\n";
        } elseif ($isGo) {
            $md .= "| Artefact | Convention | Example |\n";
            $md .= "|----------|------------|---------|\n";
            $md .= "| Exported symbol | `PascalCase` | `UserService` |\n";
            $md .= "| Unexported symbol | `camelCase` | `userRepo` |\n";
            $md .= "| Package | `lowercase` | `auth`, `order` |\n";
            $md .= "| Interface | noun + `-er` suffix | `UserStorer` |\n";
            $md .= "| Constant | `PascalCase` (exported) | `MaxRetries` |\n";
            $md .= "| API route | `kebab-case` | `/api/user-profiles` |\n\n";
        }

        // ── 5. KEY DESIGN PATTERNS ────────────────────────────────────────
        $md .= "---\n\n## 5. Key Design Patterns\n\n";

        $md .= "### Repository Pattern\n";
        $md .= "Abstract all data-access behind an interface. Use cases never call the ORM directly.\n\n";
        if ($isTypeScript) {
            $md .= "```typescript\n";
            $md .= "// domain/repositories/UserRepository.ts\n";
            $md .= "export interface UserRepository {\n";
            $md .= "  findById(id: string): Promise<User | null>\n";
            $md .= "  findByEmail(email: string): Promise<User | null>\n";
            $md .= "  save(user: User): Promise<User>\n";
            $md .= "  delete(id: string): Promise<void>\n";
            $md .= "}\n";
            $md .= "```\n\n";
        } elseif ($isPHP) {
            $md .= "```php\n";
            $md .= "// Domain/Repository/UserRepositoryInterface.php\n";
            $md .= "interface UserRepositoryInterface {\n";
            $md .= "    public function findById(string \$id): ?User;\n";
            $md .= "    public function findByEmail(string \$email): ?User;\n";
            $md .= "    public function save(User \$user): User;\n";
            $md .= "    public function delete(string \$id): void;\n";
            $md .= "}\n";
            $md .= "```\n\n";
        }

        $md .= "### Service Layer / Use Case\n";
        $md .= "Each use case is its own class with a single public `execute()` / `handle()` method.\n\n";
        $md .= "```\napplication/\n└── use-cases/\n    ├── RegisterUser.ts\n    ├── PlaceOrder.ts\n    └── CancelOrder.ts\n```\n\n";

        $md .= "### DTO — Data Transfer Objects\n";
        $md .= "Never expose your domain entities directly over HTTP. Map to/from DTOs at the interface layer.\n\n";
        $md .= "```\ninterface/ (or controller/)\n└── dto/\n    ├── RegisterUserRequest.ts    ← incoming (validated)\n    └── UserResponse.ts           ← outgoing (safe subset)\n```\n\n";

        $md .= "### Event-Driven Side Effects\n";
        $md .= "Prefer domain events over calling side-effect services directly from use cases.\n\n";
        $md .= "```\nUserRegistered (event)\n  → SendWelcomeEmail (listener)\n  → CreateDefaultWorkspace (listener)\n  → TrackSignupAnalytics (listener)\n```\n\n";

        // ── 6. API DESIGN ─────────────────────────────────────────────────
        $md .= "---\n\n## 6. API Design (REST)\n\n";
        $md .= "| Rule | Good | Bad |\n";
        $md .= "|------|------|-----|\n";
        $md .= "| Nouns for resources | `GET /users` | `GET /getUsers` |\n";
        $md .= "| Plural resource names | `/users/{id}` | `/user/{id}` |\n";
        $md .= "| HTTP verb carries the action | `DELETE /users/{id}` | `POST /users/{id}/delete` |\n";
        $md .= "| Nested for ownership | `GET /users/{id}/orders` | `GET /orders?userId={id}` |\n";
        $md .= "| Consistent error shape | `{ error, message, code }` | plain string |\n";
        $md .= "| Version the API | `/api/v1/` | `/api/` |\n\n";

        $md .= "**Standard HTTP status codes to use:**\n\n";
        $md .= "```\n200 OK              — successful GET / PUT\n";
        $md .= "201 Created         — successful POST\n";
        $md .= "204 No Content      — successful DELETE\n";
        $md .= "400 Bad Request     — validation failure\n";
        $md .= "401 Unauthorized    — missing / invalid auth token\n";
        $md .= "403 Forbidden       — authenticated but insufficient permission\n";
        $md .= "404 Not Found       — resource does not exist\n";
        $md .= "409 Conflict        — duplicate / constraint violation\n";
        $md .= "422 Unprocessable   — business rule violation\n";
        $md .= "500 Internal Error  — unexpected server failure\n";
        $md .= "```\n\n";

        // ── 7. SECURITY BASELINE ──────────────────────────────────────────
        $md .= "---\n\n## 7. Security Baseline\n\n";
        $md .= "These are **non-negotiable minimums** — apply from day one.\n\n";

        $md .= "| Area | Requirement |\n";
        $md .= "|------|-------------|\n";
        $md .= "| **Auth** | Use short-lived JWTs (15 min) + refresh tokens stored in `httpOnly` cookies |\n";
        $md .= "| **Passwords** | Hash with bcrypt / argon2 (never MD5/SHA1) |\n";
        $md .= "| **Input validation** | Validate & sanitize every incoming field at the interface layer |\n";
        $md .= "| **SQL / NoSQL injection** | Always use parameterised queries or ORM-safe methods |\n";
        $md .= "| **CORS** | Whitelist specific origins — never `*` in production |\n";
        $md .= "| **Rate limiting** | Apply on auth endpoints (10 req/min) and public APIs |\n";
        $md .= "| **Secrets** | Store in env variables / secret manager, never in source code |\n";
        $md .= "| **HTTPS** | TLS everywhere — redirect HTTP → HTTPS |\n";
        $md .= "| **Headers** | Set `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options` |\n";
        $md .= "| **Dependencies** | Run `npm audit` / `pip-audit` in CI — block on high severity |\n\n";

        if ($hasDatabase) {
            $md .= "### Database-Specific Rules\n\n";
            $md .= "- Never store plaintext sensitive data (PII, tokens, payment info)\n";
            $md .= "- Encrypt sensitive columns at rest (e.g. `pgcrypto`, column-level encryption)\n";
            $md .= "- Apply principle of least privilege: app DB user has no `DROP`/`CREATE` rights\n";
            $md .= "- Back up daily; test restore quarterly\n\n";
        }

        // ── 8. TESTING STRATEGY ───────────────────────────────────────────
        $md .= "---\n\n## 8. Testing Strategy\n\n";
        $md .= "Follow the **testing pyramid**:\n\n";
        $md .= "```\n";
        $md .= "        ▲  E2E tests (few, slow)          — user-facing flows\n";
        $md .= "       ▲▲▲  Integration tests (moderate)  — DB, HTTP, third-party\n";
        $md .= "     ▲▲▲▲▲▲▲  Unit tests (many, fast)    — pure logic, use cases\n";
        $md .= "```\n\n";

        $md .= "| Layer | What to test | Tool |\n";
        $md .= "|-------|--------------|------|\n";

        if ($isTypeScript) {
            $md .= "| Unit | Domain entities, use cases, pure functions | Vitest / Jest |\n";
            $md .= "| Integration | API routes (real DB), repository implementations | Supertest + test DB |\n";
            $md .= "| E2E | Critical user journeys (sign up, checkout) | Playwright / Cypress |\n\n";

            $md .= "**Minimum coverage targets:**\n\n";
            $md .= "- Domain layer: **90%+**\n";
            $md .= "- Application layer: **80%+**\n";
            $md .= "- Infrastructure / Interface: **60%+** (integration tests count)\n\n";
        } elseif ($isPython) {
            $md .= "| Unit | Domain entities, use cases | pytest |\n";
            $md .= "| Integration | API endpoints, repository impls | pytest + httpx + test DB |\n";
            $md .= "| E2E | Critical flows | Playwright |\n\n";
        } elseif ($isPHP) {
            $md .= "| Unit | Domain entities, use cases | PHPUnit |\n";
            $md .= "| Integration | API endpoints, Doctrine repositories | Symfony WebTestCase |\n";
            $md .= "| E2E | Critical flows | Playwright / Panther |\n\n";
        }

        $md .= "**Checklist before every PR:**\n\n";
        $md .= "- [ ] New logic has unit tests\n";
        $md .= "- [ ] No existing test is deleted without a documented reason\n";
        $md .= "- [ ] Integration test covers the happy path of every new endpoint\n";
        $md .= "- [ ] `CI` is green\n\n";

        // ── 9. GIT & CI WORKFLOW ──────────────────────────────────────────
        $md .= "---\n\n## 9. Git & CI Workflow\n\n";
        $md .= "### Branch Strategy (GitHub Flow)\n\n";
        $md .= "```\nmain           ← always deployable\n  └── feature/user-auth\n  └── fix/order-total-rounding\n  └── chore/upgrade-deps\n```\n\n";

        $md .= "### Commit Message Format (Conventional Commits)\n\n";
        $md .= "```\n<type>(<scope>): <short summary>\n\n[optional body]\n[optional footer]\n```\n\n";
        $md .= "| Type | When |\n";
        $md .= "|------|------|\n";
        $md .= "| `feat` | new user-facing feature |\n";
        $md .= "| `fix` | bug fix |\n";
        $md .= "| `refactor` | code change, no behaviour change |\n";
        $md .= "| `test` | adding or fixing tests |\n";
        $md .= "| `docs` | documentation only |\n";
        $md .= "| `chore` | tooling, deps, config |\n";
        $md .= "| `perf` | performance improvement |\n\n";

        $md .= "### CI Pipeline (minimum)\n\n";
        $md .= "```yaml\njobs:\n  quality:\n    steps:\n      - lint          # ESLint / Ruff / phpcs\n      - type-check    # tsc --noEmit / mypy / phpstan\n      - unit-tests    # fast, no DB\n      - integration   # spins up DB container\n      - security-scan # npm audit / trivy\n```\n\n";

        // ── 10. ENVIRONMENT MANAGEMENT ────────────────────────────────────
        $md .= "---\n\n## 10. Environment Management\n\n";
        $md .= "```\n.env.example       ← committed — template with dummy values\n";
        $md .= ".env.local         ← never committed — developer overrides\n";
        $md .= ".env.test          ← never committed — CI test values\n";
        $md .= "```\n\n";
        $md .= "- Use a secret manager (Vault, AWS Secrets Manager, Doppler) in production\n";
        $md .= "- Rotate credentials on every team-member departure\n";
        $md .= "- Add `.env*` (except `.env.example`) to `.gitignore` — verify with `git status`\n\n";

        $md .= "---\n\n";
        $md .= "> Generated by **ProjectAdvisor** — keep this file updated as the architecture evolves.\n";

        return $md;
    }

    private function frontendFolderTree(string $primaryId, bool $isTypeScript): string
    {
        $ext = $isTypeScript ? 'tsx' : 'jsx';
        $ts  = $isTypeScript ? 'ts'  : 'js';

        return "```\nsrc/\n├── app/                  ← routing, providers, global layout\n│   └── layout.{$ext}\n├── components/\n│   ├── ui/               ← generic reusable: Button, Input, Modal\n│   └── features/         ← domain-scoped: UserCard, OrderSummary\n├── hooks/                ← custom React hooks (useAuth, useCart)\n├── services/             ← API calls, no UI logic\n│   └── api.{$ts}\n├── store/                ← global state (Zustand / Redux slice)\n├── types/                ← shared TypeScript interfaces & enums\n├── utils/                ← pure helper functions\n├── styles/               ← global CSS / Tailwind base\n└── tests/\n    ├── unit/\n    └── integration/\n```";
    }

    private function fullstackFolderTree(string $primaryId, bool $isTypeScript): string
    {
        $ext = $isTypeScript ? 'tsx' : 'jsx';
        $ts  = $isTypeScript ? 'ts'  : 'js';

        $base = match ($primaryId) {
            'nextjs' => "```\nsrc/\n├── app/                        ← Next.js App Router\n│   ├── (public)/               ← unauthenticated pages\n│   │   ├── page.{$ext}\n│   │   └── layout.{$ext}\n│   ├── (auth)/                 ← authenticated pages\n│   │   └── dashboard/page.{$ext}\n│   └── api/                    ← Route Handlers (API)\n│       └── users/route.{$ts}\n├── components/\n│   ├── ui/                     ← generic: Button, Input\n│   └── features/               ← domain: UserCard, OrderList\n├── domain/                     ← entities, value objects, interfaces\n│   └── user/\n│       ├── User.{$ts}\n│       └── UserRepository.{$ts}\n├── application/                ← use cases\n│   └── use-cases/\n│       └── RegisterUser.{$ts}\n├── infrastructure/             ← DB, cache, external APIs\n│   └── db/\n│       └── PrismaUserRepository.{$ts}\n├── hooks/\n├── lib/                        ← shared utilities, auth config\n├── types/\n└── tests/\n    ├── unit/\n    └── integration/\n```",
            'nuxt'   => "```\n├── assets/\n├── components/\n│   ├── ui/                     ← generic\n│   └── features/               ← domain-scoped\n├── composables/                ← reusable logic (useAuth, useCart)\n├── domain/                     ← entities, interfaces\n├── application/                ← use cases\n├── server/\n│   ├── api/                    ← Nitro API routes\n│   └── middleware/\n├── stores/                     ← Pinia stores\n├── pages/                      ← file-based routing\n├── plugins/\n├── types/\n└── tests/\n```",
            default  => $this->genericFullstackTree($ext, $ts),
        };

        return $base;
    }

    private function genericFullstackTree(string $ext, string $ts): string
    {
        return "```\nsrc/\n├── interface/              ← controllers, DTOs, middleware\n├── application/            ← use cases, command handlers\n├── domain/                 ← entities, value objects, repo interfaces\n├── infrastructure/         ← DB, cache, 3rd-party implementations\n├── config/\n└── tests/\n    ├── unit/\n    └── integration/\n```";
    }

    private function backendFolderTree(string $lang, bool $isTS, bool $isPython, bool $isPHP, bool $isGo): string
    {
        if ($isTS) {
            return "```\nsrc/\n├── interface/\n│   ├── controllers/        ← HTTP handlers, input validation\n│   ├── dto/                ← request/response shapes\n│   └── middleware/         ← auth, logging, rate-limit\n├── application/\n│   └── use-cases/          ← RegisterUser, PlaceOrder…\n├── domain/\n│   ├── entities/           ← User, Order, Product\n│   ├── value-objects/      ← Email, Money, UserId\n│   └── repositories/       ← interfaces only\n├── infrastructure/\n│   ├── db/                 ← ORM models, repo implementations\n│   ├── cache/\n│   └── email/\n├── config/\n└── tests/\n    ├── unit/\n    └── integration/\n```";
        }

        if ($isPython) {
            return "```\nsrc/\n├── interface/\n│   ├── routers/            ← FastAPI routers / Django views\n│   └── schemas/            ← Pydantic schemas (request/response)\n├── application/\n│   └── use_cases/\n├── domain/\n│   ├── entities/\n│   ├── value_objects/\n│   └── repositories/       ← abstract base classes\n├── infrastructure/\n│   ├── db/                 ← SQLAlchemy models, repo impls\n│   └── email/\n├── config/\n└── tests/\n    ├── unit/\n    └── integration/\n```";
        }

        if ($isPHP) {
            return "```\nsrc/\n├── Controller/             ← HTTP layer (Symfony controllers)\n├── Application/\n│   └── Command/            ← command handlers (use cases)\n│   └── Query/              ← query handlers (CQRS read side)\n├── Domain/\n│   ├── Entity/             ← User, Order…\n│   ├── ValueObject/        ← Email, Money…\n│   └── Repository/         ← interfaces\n├── Infrastructure/\n│   ├── Doctrine/           ← entity mappings, repo impls\n│   └── Mailer/\n├── DTO/\n└── Tests/\n    ├── Unit/\n    └── Integration/\n```";
        }

        if ($isGo) {
            return "```\ncmd/\n│   └── api/main.go\ninternal/\n├── handler/            ← HTTP handlers (interface layer)\n├── service/            ← use cases / business logic\n├── domain/             ← entities, value objects\n├── repository/         ← interfaces + implementations\n├── infrastructure/     ← DB, cache, external APIs\n└── config/\npkg/                    ← shared / exported packages\ntests/\n```";
        }

        return "```\nsrc/\n├── interface/          ← controllers, routes\n├── application/        ← use cases\n├── domain/             ← entities, interfaces\n├── infrastructure/     ← DB, cache, 3rd-party\n└── tests/\n```";
    }

    private function generateProjectFile(array $stack, array $metadata): string
    {
        $primaryTech = $stack[0] ?? [];
        $objective = $metadata['objective'] ?? 'balanced';
        $profile = $metadata['profile'] ?? 'intermediate';
        $useCases = $metadata['useCases'] ?? [];

        $objectiveLabel = match ($objective) {
            'learning' => 'Apprentissage',
            'mvp' => 'MVP Rapide',
            'performance' => 'Performance',
            default => 'Équilibré',
        };

        $profileLabel = match ($profile) {
            'beginner' => 'Débutant',
            'advanced' => 'Avancé',
            default => 'Intermédiaire',
        };

        $useCasesText = implode(', ', $useCases) ?: 'Non spécifié';

        $markdown = "# Recommandation de Stack Technique\n\n";
        $markdown .= "## Informations du Projet\n\n";
        $markdown .= "| Paramètre | Valeur |\n";
        $markdown .= "|-----------|--------|\n";
        $markdown .= "| **Objectif** | {$objectiveLabel} |\n";
        $markdown .= "| **Niveau de Compétence** | {$profileLabel} |\n";
        $markdown .= "| **Use Cases** | {$useCasesText} |\n";
        $markdown .= "| **Stack Recommandée** | " . ($primaryTech['name'] ?? 'N/A') . " |\n\n";

        if (!empty($primaryTech)) {
            $markdown .= "## Stack Recommandée: " . $primaryTech['name'] . "\n\n";
            $markdown .= $primaryTech['description'] ?? '';
            $markdown .= "\n\n**Score:** " . ($primaryTech['score'] ?? 0) . "/5\n\n";
            $markdown .= "### Justification\n\n";
            $markdown .= $primaryTech['justification'] ?? 'Recommandation basée sur vos critères.';
            $markdown .= "\n\n";
        }

        $markdown .= "## Technologies Complémentaires\n\n";
        $techCount = 1;
        foreach (array_slice($stack, 1) as $tech) {
            $markdown .= "{$techCount}. **{$tech['name']}** - {$tech['description']}\n";
            $markdown .= "   - Score: {$tech['score']}/5\n";
            $markdown .= "   - Catégorie: {$tech['category']}\n\n";
            $techCount++;
        }

        return $markdown;
    }

    private function generateStackFile(array $stack): string
    {
        $markdown = "# Architecture Technique\n\n";
        $markdown .= "## Stack Sélectionnée\n\n";

        $categories = [];
        foreach ($stack as $tech) {
            $cat = $tech['category'] ?? 'other';
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $tech;
        }

        foreach ($categories as $category => $techs) {
            $markdown .= "### {$this->formatCategory($category)}\n\n";

            foreach ($techs as $tech) {
                $markdown .= "#### " . $tech['name'] . "\n\n";
                $markdown .= $tech['description'] . "\n\n";

                $markdown .= "**Caractéristiques:**\n";
                $markdown .= "- Courbe d'apprentissage: " . $this->scoreToStars($tech['learning_curve'] ?? 3) . " (" . ($tech['learning_curve'] ?? 3) . "/5)\n";
                $markdown .= "- Performance: " . $this->scoreToStars($tech['performance'] ?? 3) . " (" . ($tech['performance'] ?? 3) . "/5)\n";
                $markdown .= "- Écosystème: " . $this->scoreToStars($tech['ecosystem'] ?? 3) . " (" . ($tech['ecosystem'] ?? 3) . "/5)\n";

                if (!empty($tech['use_cases'])) {
                    $markdown .= "\n**Use Cases:**\n";
                    foreach ($tech['use_cases'] as $useCase) {
                        $markdown .= "- " . $this->formatUseCase($useCase) . "\n";
                    }
                }

                if (!empty($tech['integrations'])) {
                    $markdown .= "\n**Intégrations Natives:**\n";
                    foreach (array_slice($tech['integrations'], 0, 5) as $integration) {
                        $markdown .= "- {$integration}\n";
                    }
                }

                $markdown .= "\n**Documentation:** [{$tech['name']} Docs](" . ($tech['doc_link'] ?? $tech['documentation_url'] ?? '#') . ")\n\n";
            }
        }

        return $markdown;
    }

    private function generateConventionsFile(array $stack): string
    {
        $markdown = "# Conventions et Patterns\n\n";

        $frontendTechs = array_filter($stack, fn($t) => in_array($t['type'] ?? '', ['frontend', 'library']));
        $backendTechs = array_filter($stack, fn($t) => in_array($t['type'] ?? '', ['backend']));

        if (!empty($frontendTechs)) {
            $markdown .= "## Frontend\n\n";
            $markdown .= "### Structure de Dossiers\n\n";
            $markdown .= "```\nsrc/\n├── components/\n│   ├── common/\n│   ├── features/\n│   └── layouts/\n├── pages/\n├── hooks/\n├── services/\n├── store/\n├── types/\n├── styles/\n└── utils/\n```\n\n";

            $markdown .= "### Conventions de Nommage\n\n";
            $markdown .= "- **Composants:** `PascalCase` (ex: `UserCard.tsx`)\n";
            $markdown .= "- **Hooks:** `camelCase` avec préfixe `use` (ex: `useUserData.ts`)\n";
            $markdown .= "- **Fonctions utilitaires:** `camelCase` (ex: `formatDate.ts`)\n";
            $markdown .= "- **Variables:** `camelCase` (ex: `isLoading`)\n";
            $markdown .= "- **Constantes:** `UPPER_SNAKE_CASE` (ex: `API_BASE_URL`)\n\n";

            $markdown .= "### Patterns Recommandés\n\n";
            $markdown .= "- **Composition:** Préférer les petits composants réutilisables\n";
            $markdown .= "- **Props Drilling:** Utiliser le Context API ou une solution d'état global\n";
            $markdown .= "- **Type Safety:** Définir les types pour les props et états\n";
            $markdown .= "- **Performance:** Utiliser `memo` et `useMemo` judicieusement\n";
            $markdown .= "- **Testing:** Tests unitaires pour les hooks et composants\n\n";
        }

        if (!empty($backendTechs)) {
            $markdown .= "## Backend\n\n";
            $markdown .= "### Structure de Dossiers\n\n";
            $markdown .= "```\nsrc/\n├── controllers/\n├── services/\n├── repositories/\n├── entities/\n├── dto/\n├── middleware/\n├── config/\n├── utils/\n└── tests/\n```\n\n";

            $markdown .= "### Conventions de Nommage\n\n";
            $markdown .= "- **Classes:** `PascalCase` (ex: `UserService`)\n";
            $markdown .= "- **Méthodes:** `camelCase` (ex: `getUserById`)\n";
            $markdown .= "- **Variables:** `camelCase` (ex: `userId`)\n";
            $markdown .= "- **Constantes:** `UPPER_SNAKE_CASE` (ex: `MAX_RESULTS`)\n";
            $markdown .= "- **Routes:** `kebab-case` (ex: `/api/users/profile`)\n\n";

            $markdown .= "### Patterns Recommandés\n\n";
            $markdown .= "- **Séparation des responsabilités:** Controllers → Services → Repositories\n";
            $markdown .= "- **Validation des données:** À l'entrée des routes\n";
            $markdown .= "- **Error Handling:** Classes d'erreur personnalisées\n";
            $markdown .= "- **Logging:** Structuré et contextuel\n";
            $markdown .= "- **Tests:** Tests d'intégration et tests unitaires\n\n";
        }

        $markdown .= "## Commun\n\n";
        $markdown .= "### Git Workflow\n\n";
        $markdown .= "- **Main branch:** Branche de production\n";
        $markdown .= "- **Develop branch:** Branche de développement\n";
        $markdown .= "- **Feature branches:** `feature/description`\n";
        $markdown .= "- **Bugfix branches:** `bugfix/description`\n";
        $markdown .= "- **Messages de commit:** Format conventionnel (feat, fix, docs, style, refactor)\n\n";

        $markdown .= "### Environnements\n\n";
        $markdown .= "- **Development:** Variables locales en `.env.local`\n";
        $markdown .= "- **Staging:** Configuration intermédiaire\n";
        $markdown .= "- **Production:** Variables sécurisées en secrets du déploiement\n\n";

        return $markdown;
    }

    private function generateSetupFile(array $stack): string
    {
        $markdown = "# Guide d'Installation\n\n";

        foreach ($stack as $tech) {
            $markdown .= "## " . $tech['name'] . "\n\n";

            $markdown .= $this->generateSetupInstructions($tech);
        }

        $markdown .= "## Configuration Initiale Commune\n\n";
        $markdown .= "### 1. Installation des Dépendances\n\n";
        $markdown .= "```bash\n";
        $markdown .= "npm install\n";
        $markdown .= "```\n\n";

        $markdown .= "### 2. Configuration des Variables d'Environnement\n\n";
        $markdown .= "```bash\n";
        $markdown .= "cp .env.example .env.local\n";
        $markdown .= "# Remplir les variables d'environnement\n";
        $markdown .= "```\n\n";

        $markdown .= "### 3. Initialisation de la Base de Données\n\n";
        $markdown .= "```bash\n";
        $markdown .= "npm run db:setup\n";
        $markdown .= "```\n\n";

        $markdown .= "### 4. Démarrage en Développement\n\n";
        $markdown .= "```bash\n";
        $markdown .= "npm run dev\n";
        $markdown .= "```\n\n";

        return $markdown;
    }

    private function generateSetupInstructions(array $tech): string
    {
        $id = $tech['id'] ?? '';

        return match ($id) {
            'nextjs' => "### Prérequis\n- Node.js 18+\n- npm ou yarn\n\n### Installation\n\n```bash\nnpx create-next-app@latest --typescript\ncd project\nnpm install\n```\n\n### Démarrage\n\n```bash\nnpm run dev\n# Visiter http://localhost:3000\n```\n\n### Configuration TypeScript\n\nVérifier `tsconfig.json` pour les paramètres de compilation.\n\n",

            'nuxt' => "### Prérequis\n- Node.js 18+\n- npm ou yarn\n\n### Installation\n\n```bash\nnpx nuxi init project\ncd project\nnpm install\n```\n\n### Démarrage\n\n```bash\nnpm run dev\n# Visiter http://localhost:3000\n```\n\n### Structure Auto\n\nNuxt détecte automatiquement les dossiers `pages`, `components`, etc.\n\n",

            'astro' => "### Prérequis\n- Node.js 18+\n- npm ou yarn\n\n### Installation\n\n```bash\nnpm create astro@latest\ncd project\nnpm install\n```\n\n### Démarrage\n\n```bash\nnpm run dev\n# Visiter http://localhost:3000\n```\n\n### Intégrations\n\n```bash\nnpx astro add react\n```\n\n",

            'express' => "### Prérequis\n- Node.js 14+\n- npm ou yarn\n\n### Installation\n\n```bash\nmkdir project\ncd project\nnpm init -y\nnpm install express\n```\n\n### Démarrage\n\n```bash\nnode server.js\n# Ou avec nodemon pour le développement\nnpm install -D nodemon\nnpm run dev\n```\n\n### Structure de Base\n\n```javascript\nconst express = require('express');\nconst app = express();\napp.use(express.json());\napp.get('/api/test', (req, res) => {\n  res.json({ message: 'OK' });\n});\napp.listen(3000);\n```\n\n",

            'fastapi' => "### Prérequis\n- Python 3.7+\n- pip\n\n### Installation\n\n```bash\npip install fastapi uvicorn[standard]\n```\n\n### Démarrage\n\n```bash\nuvicorn main:app --reload\n# Visiter http://localhost:8000\n```\n\n### Structure de Base\n\n```python\nfrom fastapi import FastAPI\napp = FastAPI()\n\n@app.get(\"/api/test\")\nasync def test():\n    return {\"message\": \"OK\"}\n```\n\n",

            'django' => "### Prérequis\n- Python 3.8+\n- pip\n\n### Installation\n\n```bash\npip install django djangorestframework\ndjango-admin startproject project\ncd project\npython manage.py startapp api\n```\n\n### Démarrage\n\n```bash\npython manage.py runserver\n# Visiter http://localhost:8000\n```\n\n### Migration BD\n\n```bash\npython manage.py migrate\npython manage.py createsuperuser\n```\n\n",

            'nestjs' => "### Prérequis\n- Node.js 14+\n- npm ou yarn\n\n### Installation\n\n```bash\nnpm i -g @nestjs/cli\nnest new project\ncd project\n```\n\n### Démarrage\n\n```bash\nnpm run start:dev\n# Visiter http://localhost:3000\n```\n\n### Génération de Modules\n\n```bash\nnest generate resource users\n```\n\n",

            'laravel' => "### Prérequis\n- PHP 8.0+\n- Composer\n\n### Installation\n\n```bash\ncomposer create-project laravel/laravel project\ncd project\ncp .env.example .env\nphp artisan key:generate\n```\n\n### Démarrage\n\n```bash\nphp artisan serve\n# Visiter http://localhost:8000\n```\n\n### BD\n\n```bash\nphp artisan migrate\n```\n\n",

            'postgresql' => "### Installation\n\n#### Linux (Ubuntu/Debian)\n```bash\nsudo apt-get install postgresql postgresql-contrib\nsudo systemctl start postgresql\n```\n\n#### macOS\n```bash\nbrew install postgresql\nbrew services start postgresql\n```\n\n#### Windows\nTélécharger depuis [postgresql.org](https://www.postgresql.org/download/windows/)\n\n### Configuration\n\n```bash\nsudo -u postgres psql\nCREATE DATABASE project_db;\nCREATE USER dev WITH PASSWORD 'password';\nALTER ROLE dev SET client_encoding TO 'utf8';\nGRANT ALL PRIVILEGES ON DATABASE project_db TO dev;\n```\n\n",

            'mongodb' => "### Installation\n\n#### Docker (Recommandé)\n```bash\ndocker run -d -p 27017:27017 --name mongodb mongo\n```\n\n#### Local\nTélécharger depuis [mongodb.com/try/download](https://www.mongodb.com/try/download/community)\n\n### Configuration Node.js\n\n```bash\nnpm install mongoose\n```\n\n### Connexion\n\n```javascript\nconst mongoose = require('mongoose');\nmongoose.connect('mongodb://dev:password@localhost:27017/project_db');\n```\n\n",

            'redis' => "### Installation\n\n#### Docker (Recommandé)\n```bash\ndocker run -d -p 6379:6379 redis\n```\n\n#### Local\n```bash\nbrew install redis  # macOS\nsudo apt-get install redis-server  # Linux\n```\n\n### Configuration Node.js\n\n```bash\nnpm install redis\n```\n\n### Connexion\n\n```javascript\nconst { createClient } = require('redis');\nconst client = createClient({ url: 'redis://localhost:6379' });\nawait client.connect();\n```\n\n",

            default => "### Installation\n\nConsulter la documentation officielle: [{$tech['name']} Docs](" . ($tech['doc_link'] ?? $tech['documentation_url'] ?? '#') . ")\n\n",
        };
    }

    private function generateLibrariesFile(array $libraries): string
    {
        $markdown = "# Librairies et Outils Recommandés\n\n";

        if (empty($libraries)) {
            $markdown .= "Aucune librairie supplémentaire recommandée pour votre configuration.\n\n";
            return $markdown;
        }

        foreach ($libraries as $index => $lib) {
            $markdown .= "## " . ($index + 1) . ". " . $lib['name'] . "\n\n";
            $markdown .= "**Description:** " . $lib['purpose'] . "\n\n";
            $markdown .= "**Raison:** " . $lib['reason'] . "\n\n";

            $markdown .= $this->generateLibrarySnippets($lib);

            $markdown .= "**Documentation:** [{$lib['name']}](" . ($lib['doc_link'] ?? '#') . ")\n\n";
            $markdown .= "---\n\n";
        }

        return $markdown;
    }

    private function generateLibrarySnippets(array $lib): string
    {
        $name = strtolower($lib['name']);

        $snippets = [
            'prisma' => "### Installation\n\n```bash\nnpm install @prisma/client\nnpx prisma init\n```\n\n### Utilisation Basique\n\n```typescript\nimport { PrismaClient } from '@prisma/client';\nconst prisma = new PrismaClient();\n\nconst user = await prisma.user.create({\n  data: { email: 'user@example.com', name: 'John' }\n});\n```\n\n",

            'stripe' => "### Installation\n\n```bash\nnpm install stripe\n```\n\n### Utilisation Basique\n\n```javascript\nconst stripe = require('stripe')('sk_test_...');\n\nconst paymentIntent = await stripe.paymentIntents.create({\n  amount: 1099,\n  currency: 'usd'\n});\n```\n\n",

            'tailwind' => "### Installation\n\n```bash\nnpm install -D tailwindcss postcss autoprefixer\nnpx tailwindcss init -p\n```\n\n### Utilisation Basique\n\n```html\n<div class=\"flex items-center justify-center h-screen bg-blue-500\">\n  <h1 class=\"text-white text-4xl\">Hello World</h1>\n</div>\n```\n\n",

            'typescript' => "### Installation\n\n```bash\nnpm install -D typescript\nnpx tsc --init\n```\n\n### Utilisation Basique\n\n```typescript\ninterface User {\n  id: number;\n  name: string;\n  email: string;\n}\n\nconst user: User = {\n  id: 1,\n  name: 'John',\n  email: 'john@example.com'\n};\n```\n\n",

            'default' => "### Installation\n\n```bash\nnpm install {$lib['name']}\n```\n\n### Documentation\n\nConsulter la documentation officielle pour les détails d'utilisation.\n\n",
        ];

        foreach (array_keys($snippets) as $key) {
            if (strpos($name, $key) !== false) {
                return $snippets[$key];
            }
        }

        return $snippets['default'];
    }

    private function scoreToStars(float $score): string
    {
        $stars = (int) round($score);
        return str_repeat('⭐', max(0, min(5, $stars)));
    }

    private function formatCategory(string $category): string
    {
        return match ($category) {
            'framework' => 'Framework',
            'library' => 'Librairie',
            'database' => 'Base de Données',
            'styling' => 'Styling & CSS',
            'orm' => 'ORM',
            'payment' => 'Paiement',
            'api-style' => 'Style API',
            'language' => 'Langage',
            default => ucfirst($category),
        };
    }

    private function formatUseCase(string $useCase): string
    {
        return match ($useCase) {
            'web-app' => 'Application Web',
            'e-commerce' => 'E-commerce',
            'blog' => 'Blog',
            'landing-page' => 'Landing Page',
            'spa' => 'Single Page Application',
            'pwa' => 'Progressive Web App',
            'api' => 'API REST',
            'microservice' => 'Microservice',
            'realtime' => 'Temps Réel',
            'data-processing' => 'Traitement de Données',
            'ml-api' => 'API Machine Learning',
            'document-storage' => 'Stockage de Documents',
            'flexible-schema' => 'Schéma Flexible',
            'json-data' => 'Données JSON',
            'caching' => 'Cache',
            'sessions' => 'Sessions',
            'queues' => 'Files d\'attente',
            'relational-data' => 'Données Relationnelles',
            'complex-queries' => 'Requêtes Complexes',
            'geospatial' => 'Données Géospatiales',
            'dashboard' => 'Dashboard',
            'interactive-sites' => 'Sites Interactifs',
            'admin-panel' => 'Panneau d\'Administration',
            'cms' => 'Système de Gestion de Contenu',
            'documentation' => 'Documentation',
            'marketing-site' => 'Site Marketing',
            'graphql' => 'GraphQL',
            default => ucfirst(str_replace('-', ' ', $useCase)),
        };
    }
}
