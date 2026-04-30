import { Outlet, Link } from 'react-router-dom'
import { Zap } from 'lucide-react'
import { useTranslation } from 'react-i18next'

export default function Layout() {
  const { t, i18n } = useTranslation()

  const toggleLang = () => {
    const next = i18n.language === 'fr' ? 'en' : 'fr'
    i18n.changeLanguage(next)
    localStorage.setItem('lang', next)
  }

  return (
    <div className="min-h-screen bg-white">
      <header className="bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-lg">
        <nav className="container mx-auto px-6 py-4 flex items-center justify-between">
          <Link to="/" className="flex items-center gap-2 text-2xl font-bold hover:opacity-90 transition">
            <Zap className="w-7 h-7" />
            ProjectAdvisor
          </Link>
          <div className="flex items-center gap-6">
            <Link to="/" className="hover:opacity-80 transition font-medium">
              {t('nav.home')}
            </Link>
            <Link to="/compare" className="hover:opacity-80 transition font-medium">
              {t('nav.comparator')}
            </Link>
            <button
              onClick={toggleLang}
              className="px-3 py-1 rounded-md border border-white/40 text-sm font-bold hover:bg-white/20 transition"
            >
              {t('lang_switch')}
            </button>
          </div>
        </nav>
      </header>

      <main className="container mx-auto px-6 py-8">
        <Outlet />
      </main>

      <footer className="bg-gray-100 border-t border-gray-200 mt-12">
        <div className="container mx-auto px-6 py-8 text-center text-gray-600">
          <p>{t('footer.copyright')}</p>
        </div>
      </footer>
    </div>
  )
}
