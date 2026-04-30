# ProjectAdvisor Frontend - Installation Guide

## System Requirements

- **Node.js**: v16.0.0 or higher (check with `node --version`)
- **npm**: v7.0.0 or higher (check with `npm --version`)
- **Disk Space**: ~500MB (for node_modules + build artifacts)

## Installation Steps

### Step 1: Navigate to Frontend Directory

```bash
cd /sessions/eager-zealous-wozniak/mnt/ProjectAdvisor/project-advisor/frontend
```

### Step 2: Install Dependencies

If you have access to npm registry (standard setup):

```bash
npm install
```

**If npm install fails with 403 errors:**

This typically indicates a npm registry issue. Try these solutions:

**Option A: Use npm force flag**
```bash
npm install --force
```

**Option B: Clear npm cache**
```bash
npm cache clean --force
npm install
```

**Option C: Use alternative registry**
```bash
npm install --registry https://registry.npmjs.org/
```

**Option D: Use Yarn instead of npm**
```bash
yarn install
```

### Step 3: Configure Environment

Create a `.env` file from the template:

```bash
cp .env.example .env
```

Edit `.env` if needed to point to your backend API:

```env
VITE_API_URL=http://localhost:8080/api
```

### Step 4: Verify Installation

Check that all dependencies are installed:

```bash
npm list react react-dom react-router-dom axios
```

Should show versions:
- react@^18.3.1
- react-dom@^18.3.1
- react-router-dom@^6.22.0
- axios@^1.6.5

### Step 5: Run Development Server

```bash
npm run dev
```

You should see output like:

```
> vite
  VITE v5.0.8  ready in XXX ms
  ➜  Local:   http://localhost:5173/
  ➜  press h to show help
```

The app will be available at:
- Development: `http://localhost:5173/`
- With proxy: `http://localhost:5173/app/` (configured in vite.config.js)

### Step 6: Test the Application

Open your browser and navigate to `http://localhost:5173`

You should see:
1. ProjectAdvisor header with logo
2. Home page with hero section
3. "Start Your Journey" button linking to questionnaire
4. Three feature cards
5. "How It Works" section
6. CTA section

### Step 7: Build for Production

When ready to deploy:

```bash
npm run build
```

This creates:
- `dist/` folder with optimized assets
- Ready for deployment to Vercel, AWS, or your server

## Docker Setup (Optional)

If you prefer to run in Docker:

### Using Docker Compose (Recommended)

From the project root directory:

```bash
docker-compose -f docker-compose.dev.yml up frontend
```

### Manual Docker Build

```bash
cd frontend

# Build image
docker build -t project-advisor-frontend:latest .

# Run container
docker run -p 5173:5173 project-advisor-frontend:latest npm run dev
```

## Troubleshooting

### Issue: "Cannot find module 'react'"

**Solution:**
```bash
rm -rf node_modules package-lock.json
npm install
```

### Issue: "Port 5173 already in use"

**Solution:**
```bash
# Use different port
npm run dev -- --port 3000

# Or kill the process using port 5173
lsof -i :5173
kill -9 <PID>
```

### Issue: "VITE_API_URL not working"

**Solution:**
1. Check that `.env` file exists in the `frontend/` directory
2. Verify the variable name is exactly `VITE_API_URL` (must start with `VITE_`)
3. Restart dev server after changing `.env`
4. Variables are loaded at build time, not runtime

### Issue: "API calls return 404"

**Solution:**
1. Ensure backend is running on http://localhost:8080
2. Check CORS configuration on backend
3. Verify API endpoints match expected paths in `src/services/api.js`
4. Use browser DevTools Network tab to inspect requests

### Issue: "React version mismatch"

**Solution:**
```bash
npm ls react react-dom
# Should all be ^18.3.1

npm install react@18.3.1 react-dom@18.3.1
```

### Issue: "Tailwind styles not appearing"

**Solution:**
1. Check that `src/index.css` imports Tailwind directives
2. Verify `tailwind.config.js` includes content paths:
   ```js
   content: ["./index.html", "./src/**/*.{js,jsx}"]
   ```
3. Restart dev server
4. Clear browser cache (Ctrl+Shift+Delete)

## Development Workflow

### Making Code Changes

1. Edit files in `src/` directory
2. Save changes
3. Browser automatically reloads (Hot Module Replacement)

### Adding New Dependencies

```bash
npm install package-name
npm install --save-dev package-name  # for dev dependencies
```

### Running Production Build Locally

```bash
npm run build
npm run preview
```

Visit `http://localhost:4173` to see production build

## IDE/Editor Setup

### VS Code Recommended Extensions

- ES7+ React/Redux/React-Native snippets
- Tailwind CSS IntelliSense
- Prettier - Code formatter
- ESLint

### Create .vscode/settings.json

```json
{
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "tailwindCSS.experimental.classRegex": [
    ["cva\\(([^)]*)\\)", "(?:'|\"|`)([^']*)(?:'|\"|`)"]
  ]
}
```

## API Integration

The frontend is pre-configured to work with the backend API:

### Backend Requirements

Ensure your backend at `http://localhost:8080` provides:

1. **POST /api/recommendations**
   - Request: Questionnaire answers
   - Response: `{ id, recommendations, libraries }`

2. **GET /api/recommendations/{id}**
   - Response: Full recommendation data

3. **GET /api/recommendations/{id}/files**
   - Response: `{ files: [{ filename, content }, ...] }`

4. **GET /api/recommendations/{id}/download**
   - Response: ZIP file download

5. **GET /api/technologies**
   - Response: `{ technologies: [...] }`

6. **POST /api/compare**
   - Request: `{ technologies: [id1, id2, ...] }`
   - Response: Comparison data

7. **POST /api/advice-requests**
   - Request: `{ name, email, subject, message }`

## Performance Notes

- **Initial Load**: ~150KB gzipped (without dependencies)
- **Chunk Size**: Code is split by page routes
- **Cache**: Built assets have content hash for browser caching
- **Images**: Optimize before adding (use WebP format)

## Security Considerations

- API key never stored in frontend code
- All API calls go through Axios instance
- CORS must be properly configured on backend
- Environment variables for sensitive data

## Next Steps

1. ✅ Install dependencies
2. ✅ Start development server
3. ✅ Verify frontend loads
4. 🔄 Integrate with backend API
5. 🔄 Run tests (if adding)
6. 🔄 Build for production
7. 🔄 Deploy to hosting

## Support

- **Node.js Issues**: Visit https://nodejs.org/
- **Vite Docs**: https://vitejs.dev/
- **React Docs**: https://react.dev/
- **Tailwind Docs**: https://tailwindcss.com/
- **React Router**: https://reactrouter.com/

## Getting Help

Check logs for detailed error messages:

```bash
# Run with verbose output
npm run dev --verbose

# Check npm logs
cat ~/.npm/_logs/latest-debug-log.txt
```

Enjoy building with ProjectAdvisor! 🚀
