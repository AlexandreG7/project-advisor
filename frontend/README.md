# ProjectAdvisor Frontend

A modern React 18 + Vite + Tailwind CSS application that helps developers choose the perfect tech stack for their projects.

## Features

- **Interactive Questionnaire**: 5-phase wizard to understand your project needs, constraints, and preferences
- **Smart Recommendations**: AI-powered stack recommendations based on your profile
- **Technology Comparator**: Compare 2-3 technologies side-by-side across multiple criteria
- **Detailed Results**: View recommended technologies with scores, pros/cons, and library suggestions
- **File Downloads**: Export recommendations as markdown files for use with AI assistants
- **Advice Requests**: Request personalized advice from experts

## Stack

- **Frontend Framework**: React 18.3
- **Build Tool**: Vite 5.0
- **Styling**: Tailwind CSS 4.0
- **Routing**: React Router v6
- **HTTP Client**: Axios
- **Icons**: Lucide React
- **Markdown**: React Markdown

## Getting Started

### Prerequisites

- Node.js 16+
- npm or yarn

### Installation

```bash
cd frontend
npm install
```

### Development

Start the development server:

```bash
npm run dev
```

The app will be available at `http://localhost:5173`

### Build

Build for production:

```bash
npm run build
```

Preview production build:

```bash
npm run preview
```

## Project Structure

```
src/
├── components/
│   └── Layout.jsx          # Main layout with header, footer, routing
├── pages/
│   ├── Home.jsx            # Landing page
│   ├── Questionnaire.jsx   # 5-phase form wizard
│   ├── Results.jsx         # Recommendations display
│   └── Comparator.jsx      # Technology comparator
├── services/
│   └── api.js              # API client and endpoints
├── App.jsx                 # App routes
├── main.jsx                # React entry point
└── index.css               # Tailwind CSS imports
```

## Environment Variables

Create a `.env` file (copy from `.env.example`):

```env
VITE_API_URL=http://localhost:8080/api
```

## API Endpoints Used

- `POST /api/recommendations` - Submit questionnaire
- `GET /api/recommendations/{id}` - Get recommendations
- `GET /api/recommendations/{id}/files` - Get markdown files
- `GET /api/recommendations/{id}/download` - Download recommendations
- `GET /api/technologies` - Get all technologies
- `POST /api/compare` - Compare technologies
- `POST /api/advice-requests` - Submit advice request

## Customization

### Colors

Tailwind color palette can be customized in `tailwind.config.js`:
- Primary: Blue (blue-600, blue-700)
- Secondary: Violet (violet-500, violet-600)

### Forms & Validation

Each questionnaire phase has built-in validation. Add custom validation in `src/pages/Questionnaire.jsx`.

## Browser Support

- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions

## License

MIT
