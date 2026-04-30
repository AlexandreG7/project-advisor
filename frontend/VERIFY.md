# Frontend Installation Verification Checklist

Use this document to verify that your ProjectAdvisor frontend installation is complete and working correctly.

## File Structure Verification

Check that all required files exist:

```bash
cd /sessions/eager-zealous-wozniak/mnt/ProjectAdvisor/project-advisor/frontend
```

### Configuration Files (should exist)

- [ ] `package.json` - Project metadata and dependencies
- [ ] `vite.config.js` - Vite build configuration
- [ ] `tailwind.config.js` - Tailwind CSS configuration
- [ ] `postcss.config.js` - PostCSS configuration
- [ ] `.eslintrc.cjs` - ESLint rules
- [ ] `.gitignore` - Git ignore rules
- [ ] `.dockerignore` - Docker ignore rules
- [ ] `.env.example` - Environment template
- [ ] `Dockerfile` - Docker build file
- [ ] `index.html` - HTML entry point

Verify with:
```bash
ls -la *.json *.js *.cjs 2>/dev/null | wc -l
# Should show at least 10 files
```

### Source Files (src/ directory)

- [ ] `src/main.jsx` - React entry point
- [ ] `src/index.css` - Tailwind imports
- [ ] `src/App.jsx` - Routes definition
- [ ] `src/components/Layout.jsx` - Main layout
- [ ] `src/pages/Home.jsx` - Home page
- [ ] `src/pages/Questionnaire.jsx` - Form wizard
- [ ] `src/pages/Results.jsx` - Results display
- [ ] `src/pages/Comparator.jsx` - Comparator page
- [ ] `src/services/api.js` - API client

Verify with:
```bash
find src -type f | wc -l
# Should show 9 files
```

### Documentation Files

- [ ] `README.md` - Main documentation
- [ ] `SETUP.md` - Setup guide
- [ ] `INSTALLATION.md` - Installation steps
- [ ] `VERIFY.md` - This file

## Content Verification

### Check React Entry Point

```bash
grep -l "createRoot" src/main.jsx
# Should find the import
```

### Check Routes

```bash
grep -l "Routes" src/App.jsx
# Should find Routes component
```

### Check Tailwind CSS

```bash
grep "@tailwind" src/index.css
# Should show three @tailwind directives
```

### Check API Service

```bash
grep -l "submitQuestionnaire" src/services/api.js
# Should find all API functions
```

### Check Components

```bash
grep -l "export default" src/components/Layout.jsx src/pages/*.jsx
# Should find 5 components
```

## Installation Steps to Complete

After file verification, complete installation:

### Step 1: Install Node Modules

```bash
cd /sessions/eager-zealous-wozniak/mnt/ProjectAdvisor/project-advisor/frontend
npm install
```

**Expected output:**
```
added XXX packages
```

**Verify success:**
```bash
ls -la node_modules | head -5
# Should show node_modules directory with dependencies
```

### Step 2: Create Environment File

```bash
cp .env.example .env
```

**Verify:**
```bash
cat .env
# Should show VITE_API_URL
```

### Step 3: Verify Dependencies

```bash
npm ls react react-dom react-router-dom axios lucide-react
```

**Expected output:**
```
project-advisor-frontend@1.0.0
├── react@18.3.1
├── react-dom@18.3.1
├── react-router-dom@6.22.0
├── axios@1.6.5
└── lucide-react@0.340.0
```

### Step 4: Start Development Server

```bash
npm run dev
```

**Expected output:**
```
VITE v5.0.8 ready in XXX ms
  ➜  Local:   http://localhost:5173/
  ➜  press h to show help
```

**In your browser, verify:**
- [ ] Page loads without errors
- [ ] ProjectAdvisor logo visible in header
- [ ] Hero section displays correctly
- [ ] "Start Your Journey" button is clickable
- [ ] Navigation links work (Home, Comparator)
- [ ] Footer visible at bottom
- [ ] No console errors (F12 → Console)

### Step 5: Test Navigation

Navigate to these routes and verify they load:

- [ ] `http://localhost:5173/` - Home page
- [ ] `http://localhost:5173/questionnaire` - Questionnaire (5 phases)
  - Phase 1: Experience + Languages
  - Phase 2: Goal + Project Type
  - Phase 3: Features
  - Phase 4: Constraints
  - Phase 5: Preferences
- [ ] `http://localhost:5173/compare` - Comparator page

### Step 6: Test Form Interactions

On the Questionnaire page:

- [ ] Click on different experience levels (buttons highlight)
- [ ] Toggle language checkboxes (can select multiple)
- [ ] Progress bar increases when clicking Next
- [ ] Error message appears if trying to proceed without required fields
- [ ] Previous button disabled on phase 1
- [ ] Next button progresses through phases
- [ ] Final phase shows "Get Recommendations" button

On the Comparator page:

- [ ] Search input works
- [ ] Can select technologies
- [ ] Selected tech shown as cards
- [ ] Can remove selected tech with X button
- [ ] Compare button disabled until 2+ selected

## Browser Console Verification

Open browser DevTools (F12) → Console tab:

**Should see:**
- No red error messages
- No "Uncaught" errors
- Possibly some warnings (acceptable)

**Should NOT see:**
- 404 errors from `/api` (expected if backend not running)
- Module import errors
- React version warnings

## API Integration Check (Optional)

If backend is running:

1. Start frontend: `npm run dev`
2. Open DevTools → Network tab
3. Go to `/questionnaire`
4. Fill out all 5 phases
5. Click "Get Recommendations"

**Should see:**
- [ ] POST request to `/api/recommendations`
- [ ] Response status 200 or 201
- [ ] Redirect to `/results/{id}`
- [ ] Results page loads data from `/api/recommendations/{id}`

## Build Verification

Test production build:

```bash
npm run build
```

**Expected output:**
```
✓ built in XXX ms
```

**Verify:**
```bash
ls -la dist/
# Should show index.html and assets folder
```

**Preview build:**
```bash
npm run preview
```

**In browser:**
- Should load at `http://localhost:4173`
- All features should work same as dev mode
- Assets should load from `/app/` path (due to base config)

## Performance Checks

### Check Build Size

```bash
npm run build 2>&1 | grep "gzip"
# Should see gzipped sizes
```

**Expected:**
- `index.html` < 2KB
- Main JS bundle < 200KB (gzipped)

### Check for Unused CSS

Tailwind should only include CSS for used classes:

```bash
ls -lah dist/assets/
# CSS file should be < 30KB
```

## Docker Verification (Optional)

If using Docker:

```bash
# Build image
docker build -t project-advisor-frontend .

# Run container
docker run -p 5173:5173 project-advisor-frontend

# In browser, visit http://localhost:5173
```

**Should see:**
- [ ] Application loads normally
- [ ] All assets and styles load
- [ ] No console errors

## Troubleshooting Checklist

If any checks fail:

### Files Missing?
```bash
find . -type f -name "*.jsx" -o -name "*.js" | wc -l
# Should show 13+ files
```

### Dependencies Not Installed?
```bash
npm install --force
```

### Port Already in Use?
```bash
npm run dev -- --port 3000
```

### Styles Not Loading?
```bash
rm -rf node_modules/.vite
npm run dev
```

### API Calls Failing?
```bash
# Check backend is running
curl http://localhost:8080/api/technologies
# Should return JSON (or 404 if endpoint missing)
```

## Completion Checklist

When all verifications pass, check these off:

- [ ] All files present in correct locations
- [ ] npm install completed successfully
- [ ] .env file created
- [ ] npm run dev starts without errors
- [ ] Browser loads home page correctly
- [ ] Navigation works (Home, Comparator)
- [ ] Questionnaire form interactions work
- [ ] All 5 phases accessible
- [ ] No console errors in browser
- [ ] Production build (npm run build) succeeds
- [ ] Preview build (npm run preview) works

## Final Status

Once all items are checked, your frontend is ready!

```
✅ File structure complete
✅ Dependencies installed
✅ Environment configured
✅ Development server running
✅ All pages accessible
✅ Forms interactive
✅ Production build successful
✅ Ready for backend integration
```

## Next Phase

Once backend API is running, verify these endpoint calls work:

1. Submit questionnaire → GET /results/{id}
2. Fetch recommendations → Display results page
3. Submit advice request → Form clears
4. Compare technologies → View comparison table
5. Download files → ZIP downloads

For detailed API integration, see backend documentation.

Happy coding! 🚀
