# ProjectAdvisor API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

Currently, the API does not require authentication. This is suitable for development and can be secured later with JWT or OAuth.

---

## Endpoints Overview

### Health Check
- `GET /health` - Check API status

### Recommendations
- `POST /recommendations` - Generate a new recommendation
- `GET /recommendations/{id}` - Get a recommendation by ID
- `GET /recommendations/{id}/files` - Get generated markdown files
- `GET /recommendations/{id}/download` - Download ZIP archive

### Technologies
- `GET /technologies` - List all technologies
- `GET /technologies/{id}` - Get technology details
- `POST /compare` - Compare multiple technologies

### Advice Requests
- `POST /advice-requests` - Create an advice request
- `GET /advice-requests` - List advice requests
- `GET /advice-requests/{id}` - Get advice request details

---

## Detailed Endpoints

### 1. Health Check

#### `GET /health`

Check if the API is running and healthy.

**Response (200 OK):**
```json
{
  "status": "ok",
  "timestamp": "2024-03-26T12:00:00+00:00",
  "service": "ProjectAdvisor API"
}
```

**cURL Example:**
```bash
curl http://localhost:8000/api/health
```

---

### 2. Generate Recommendation

#### `POST /recommendations`

Generate a stack recommendation based on questionnaire answers.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "objective": "mvp",
  "profile": "intermediate",
  "useCases": ["web-app", "e-commerce"],
  "features": ["typescript", "tailwind"],
  "constraints": [],
  "preferences": []
}
```

**Parameters:**

| Name | Type | Required | Values | Description |
|------|------|----------|--------|-------------|
| objective | string | Yes | learning, mvp, performance, balanced | Project objective |
| profile | string | Yes | beginner, intermediate, advanced | Developer profile |
| useCases | array | No | web-app, api, spa, pwa, blog, etc. | Intended use cases |
| features | array | No | typescript, tailwind, stripe, etc. | Preferred technologies |
| constraints | array | No | no-database, no-backend | Constraints |
| preferences | array | No | Any technology ID | Preferred technologies |

**Response (201 Created):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "result": {
    "stack": [
      {
        "id": "nextjs",
        "name": "Next.js",
        "category": "framework",
        "type": "frontend",
        "description": "React framework for production",
        "score": 4.5,
        "justification": "Excellent for MVP rapid development"
      }
    ],
    "libraries": [
      {
        "name": "Tailwind CSS",
        "purpose": "Utility-first CSS framework",
        "reason": "Integration recommended with Next.js",
        "doc_link": "https://tailwindcss.com"
      }
    ],
    "summary": "Next.js is recommended for rapid MVP development..."
  },
  "createdAt": "2024-03-26T12:00:00+00:00"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/recommendations \
  -H "Content-Type: application/json" \
  -d '{
    "objective": "mvp",
    "profile": "intermediate",
    "useCases": ["web-app"],
    "features": ["typescript"],
    "constraints": [],
    "preferences": []
  }'
```

---

### 3. Get Recommendation

#### `GET /recommendations/{id}`

Retrieve a previously generated recommendation.

**Parameters:**
- `id` (string, path) - Recommendation UUID

**Response (200 OK):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "result": { ... },
  "answers": { ... },
  "createdAt": "2024-03-26T12:00:00+00:00"
}
```

**Error Responses:**
- `404 Not Found` - Recommendation doesn't exist

**cURL Example:**
```bash
curl http://localhost:8000/api/recommendations/550e8400-e29b-41d4-a716-446655440000
```

---

### 4. Get Generated Files

#### `GET /recommendations/{id}/files`

Get the 5 markdown files generated for a recommendation.

**Parameters:**
- `id` (string, path) - Recommendation UUID

**Response (200 OK):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "files": [
    {
      "name": "PROJECT.md",
      "content": "# Project...",
      "size": 1024
    },
    {
      "name": "STACK.md",
      "content": "# Stack...",
      "size": 2048
    }
  ],
  "count": 5
}
```

**Files Generated:**
1. **PROJECT.md** - Project overview and objectives
2. **STACK.md** - Technology stack details
3. **CONVENTIONS.md** - Code conventions and patterns
4. **SETUP.md** - Installation and setup guide
5. **LIBRARIES.md** - Recommended libraries and usage

**cURL Example:**
```bash
curl http://localhost:8000/api/recommendations/550e8400-e29b-41d4-a716-446655440000/files
```

---

### 5. Download Recommendation Package

#### `GET /recommendations/{id}/download`

Download a ZIP file containing all generated markdown files.

**Parameters:**
- `id` (string, path) - Recommendation UUID

**Response (200 OK):**
- Content-Type: `application/zip`
- ZIP file containing:
  - PROJECT.md
  - STACK.md
  - CONVENTIONS.md
  - SETUP.md
  - LIBRARIES.md
  - METADATA.json

**cURL Example:**
```bash
curl -O http://localhost:8000/api/recommendations/550e8400-e29b-41d4-a716-446655440000/download
# Downloads as: ProjectAdvisor-550e8400-e29b-41d4-a716-446655440000.zip
```

---

### 6. List All Technologies

#### `GET /technologies`

List all available technologies with optional filtering.

**Query Parameters:**
- `category` (optional) - Filter by category

**Response (200 OK):**
```json
{
  "total": 20,
  "categories": ["framework", "library", "database", "styling"],
  "technologies": {
    "framework": [
      {
        "id": "nextjs",
        "name": "Next.js",
        "category": "framework",
        "type": "frontend",
        "description": "React framework for production",
        "learning_curve": 2,
        "performance": 4.5,
        "ecosystem": 5,
        "use_cases": ["web-app", "spa"],
        "integrations": ["typescript", "tailwind"],
        "doc_link": "https://nextjs.org/docs"
      }
    ]
  },
  "all": [ ... ]
}
```

**cURL Example:**
```bash
curl http://localhost:8000/api/technologies
curl http://localhost:8000/api/technologies?category=framework
```

---

### 7. Get Technology Details

#### `GET /technologies/{id}`

Get detailed information about a specific technology.

**Parameters:**
- `id` (string, path) - Technology ID (e.g., "nextjs")

**Response (200 OK):**
```json
{
  "id": "nextjs",
  "name": "Next.js",
  "category": "framework",
  "type": "frontend",
  "description": "React framework for production",
  "learning_curve": 2,
  "performance": 4.5,
  "ecosystem": 5,
  "use_cases": ["web-app", "e-commerce", "spa"],
  "integrations": ["typescript", "tailwind", "prisma"],
  "doc_link": "https://nextjs.org/docs",
  "keywords": ["react", "ssr", "api-routes"]
}
```

**Error Responses:**
- `404 Not Found` - Technology doesn't exist

**cURL Example:**
```bash
curl http://localhost:8000/api/technologies/nextjs
curl http://localhost:8000/api/technologies/express
curl http://localhost:8000/api/technologies/postgresql
```

---

### 8. Compare Technologies

#### `POST /compare`

Compare multiple technologies side-by-side.

**Request Body:**
```json
{
  "ids": ["nextjs", "nuxt", "astro"]
}
```

**Parameters:**
- `ids` (array, required) - Array of technology IDs (2-5 technologies)

**Response (200 OK):**
```json
{
  "technologies": [
    { "id": "nextjs", "name": "Next.js", ... },
    { "id": "nuxt", "name": "Nuxt", ... },
    { "id": "astro", "name": "Astro", ... }
  ],
  "comparison": {
    "learning_curve": {
      "Next.js": 2,
      "Nuxt": 2,
      "Astro": 2
    },
    "performance": {
      "Next.js": 4.5,
      "Nuxt": 4.3,
      "Astro": 5
    },
    "ecosystem": {
      "Next.js": 5,
      "Nuxt": 4.5,
      "Astro": 3.5
    }
  },
  "count": 3
}
```

**Error Responses:**
- `400 Bad Request` - Invalid request or wrong number of technologies

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/compare \
  -H "Content-Type: application/json" \
  -d '{
    "ids": ["nextjs", "nuxt", "astro"]
  }'
```

---

### 9. Create Advice Request

#### `POST /advice-requests`

Submit a question or advice request.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Question about tech stack",
  "message": "I want to build an e-commerce site...",
  "recommendationId": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| name | string | No | User's name |
| email | string | Yes | User's email |
| subject | string | Yes | Request subject |
| message | string | Yes | Request message |
| recommendationId | string | No | Related recommendation UUID |
| questionnaireSnapshot | object | No | Questionnaire data |

**Response (201 Created):**
```json
{
  "id": 1,
  "email": "john@example.com",
  "status": "pending",
  "createdAt": "2024-03-26T12:00:00+00:00"
}
```

**Error Responses:**
- `400 Bad Request` - Invalid email or missing fields

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/advice-requests \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "Stack recommendation",
    "message": "I need help with my project"
  }'
```

---

### 10. List Advice Requests

#### `GET /advice-requests`

List all advice requests with optional filtering.

**Query Parameters:**
- `status` (optional) - Filter by status (pending, answered, archived)
- `email` (optional) - Filter by email address

**Response (200 OK):**
```json
{
  "total": 5,
  "requests": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "subject": "Stack recommendation",
      "status": "pending",
      "createdAt": "2024-03-26T12:00:00+00:00"
    }
  ]
}
```

**cURL Examples:**
```bash
curl http://localhost:8000/api/advice-requests
curl http://localhost:8000/api/advice-requests?status=pending
curl http://localhost:8000/api/advice-requests?email=john@example.com
```

---

### 11. Get Advice Request

#### `GET /advice-requests/{id}`

Get details of a specific advice request.

**Parameters:**
- `id` (integer, path) - Advice request ID

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Stack recommendation",
  "message": "I need help choosing a tech stack...",
  "status": "pending",
  "recommendationId": "550e8400-e29b-41d4-a716-446655440000",
  "createdAt": "2024-03-26T12:00:00+00:00",
  "answeredAt": null
}
```

**cURL Example:**
```bash
curl http://localhost:8000/api/advice-requests/1
```

---

## Error Handling

All errors are returned as JSON with appropriate HTTP status codes:

### Common Error Responses

**400 Bad Request:**
```json
{
  "error": "Invalid request",
  "errors": {
    "email": "Invalid email format"
  }
}
```

**404 Not Found:**
```json
{
  "error": "Recommendation not found"
}
```

**500 Internal Server Error:**
```json
{
  "error": "Internal server error"
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 404 | Not Found |
| 500 | Server Error |

---

## Rate Limiting

Currently, there is no rate limiting. This will be added in future versions.

---

## Version Information

- **API Version**: v1
- **Backend**: Symfony 8
- **Database**: SQLite
- **Last Updated**: March 2024

---

## Example Workflows

### Workflow 1: Generate Recommendation and Download Files

```bash
# 1. Generate recommendation
RESPONSE=$(curl -s -X POST http://localhost:8000/api/recommendations \
  -H "Content-Type: application/json" \
  -d '{
    "objective": "mvp",
    "profile": "intermediate",
    "useCases": ["web-app"],
    "features": ["typescript"],
    "constraints": [],
    "preferences": []
  }')

ID=$(echo $RESPONSE | grep -o '"id":"[^"]*' | head -1 | cut -d'"' -f4)

# 2. View generated files
curl http://localhost:8000/api/recommendations/$ID/files

# 3. Download ZIP
curl -O http://localhost:8000/api/recommendations/$ID/download
```

### Workflow 2: Compare Technologies and Submit Advice

```bash
# 1. Compare technologies
curl -X POST http://localhost:8000/api/compare \
  -H "Content-Type: application/json" \
  -d '{"ids": ["nextjs", "nuxt", "astro"]}'

# 2. Submit advice request
curl -X POST http://localhost:8000/api/advice-requests \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John",
    "email": "john@example.com",
    "subject": "Follow-up question",
    "message": "Which framework is best for..."
  }'
```

---

## Support and Feedback

For API issues or feature requests, please contact the development team or file an issue in the project repository.
