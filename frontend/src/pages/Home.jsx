import { Link } from 'react-router-dom'
import { Zap, Target, Lightbulb, Rocket } from 'lucide-react'
import { useTranslation } from 'react-i18next'

export default function Home() {
  const { t } = useTranslation()

  const steps = t('home.how_it_works.steps', { returnObjects: true })

  return (
    <div className="space-y-16">
      <section className="bg-gradient-to-r from-blue-600 via-blue-700 to-violet-600 text-white rounded-2xl p-16 text-center shadow-xl">
        <h1 className="text-5xl font-bold mb-4">{t('home.hero.title')}</h1>
        <p className="text-xl text-blue-100 mb-8">{t('home.hero.subtitle')}</p>
        <p className="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">{t('home.hero.description')}</p>
        <Link
          to="/questionnaire"
          className="inline-block bg-white text-blue-600 font-bold px-8 py-4 rounded-lg hover:bg-blue-50 transition shadow-lg text-lg"
        >
          {t('home.hero.cta')}
        </Link>
      </section>

      <section className="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div className="bg-white border border-gray-200 rounded-xl p-8 shadow-md hover:shadow-lg transition">
          <div className="bg-blue-100 w-16 h-16 rounded-xl flex items-center justify-center mb-4">
            <Lightbulb className="w-8 h-8 text-blue-600" />
          </div>
          <h3 className="text-xl font-bold mb-3 text-gray-900">{t('home.features.smart_analysis.title')}</h3>
          <p className="text-gray-700">{t('home.features.smart_analysis.desc')}</p>
        </div>

        <div className="bg-white border border-gray-200 rounded-xl p-8 shadow-md hover:shadow-lg transition">
          <div className="bg-violet-100 w-16 h-16 rounded-xl flex items-center justify-center mb-4">
            <Target className="w-8 h-8 text-violet-600" />
          </div>
          <h3 className="text-xl font-bold mb-3 text-gray-900">{t('home.features.customized_stacks.title')}</h3>
          <p className="text-gray-700">{t('home.features.customized_stacks.desc')}</p>
        </div>

        <div className="bg-white border border-gray-200 rounded-xl p-8 shadow-md hover:shadow-lg transition">
          <div className="bg-blue-100 w-16 h-16 rounded-xl flex items-center justify-center mb-4">
            <Rocket className="w-8 h-8 text-blue-600" />
          </div>
          <h3 className="text-xl font-bold mb-3 text-gray-900">{t('home.features.ready_to_deploy.title')}</h3>
          <p className="text-gray-700">{t('home.features.ready_to_deploy.desc')}</p>
        </div>
      </section>

      <section className="bg-gray-50 rounded-2xl p-12">
        <h2 className="text-3xl font-bold mb-12 text-center text-gray-900">{t('home.how_it_works.title')}</h2>
        <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
          {Array.isArray(steps) && steps.map((step, i) => (
            <div key={i} className="text-center">
              <div className="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-lg font-bold shadow-md">
                {i + 1}
              </div>
              <h4 className="font-bold text-gray-900 mb-2">{step.title}</h4>
              <p className="text-sm text-gray-600">{step.desc}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="bg-gradient-to-r from-violet-50 to-blue-50 rounded-2xl p-12 border border-violet-200 text-center">
        <h2 className="text-3xl font-bold mb-4 text-gray-900">{t('home.cta.title')}</h2>
        <p className="text-lg text-gray-700 mb-8">{t('home.cta.desc')}</p>
        <Link
          to="/questionnaire"
          className="inline-block bg-blue-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg"
        >
          {t('home.cta.button')}
        </Link>
      </section>
    </div>
  )
}
