import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import i18n from '../i18n'
import { getRecommendation, getRecommendationFiles, submitAdviceRequest, downloadRecommendation } from '../services/api'
import ReactMarkdown from 'react-markdown'
import { Star, Download, Mail, ChevronRight, Loader, AlertCircle } from 'lucide-react'

function ScoreBar({ value, max = 5, color = 'blue' }) {
  const pct = Math.round((value / max) * 100)
  const colors = {
    blue: 'bg-blue-500', green: 'bg-green-500', amber: 'bg-amber-500',
    violet: 'bg-violet-500', red: 'bg-red-400',
  }
  return (
    <div className="flex items-center gap-2">
      <div className="flex-1 bg-gray-100 rounded-full h-2">
        <div className={`${colors[color]} h-2 rounded-full`} style={{ width: `${pct}%` }} />
      </div>
      <span className="text-xs font-semibold text-gray-600 w-6 text-right">{value}</span>
    </div>
  )
}

const ROLE_STYLES = {
  fullstack: { bg: 'from-violet-500 to-blue-500',   badge: 'bg-violet-100 text-violet-800', icon: '⚡' },
  frontend:  { bg: 'from-blue-500 to-cyan-500',     badge: 'bg-blue-100 text-blue-800',     icon: '🎨' },
  backend:   { bg: 'from-green-500 to-emerald-600', badge: 'bg-green-100 text-green-800',   icon: '⚙️' },
  database:  { bg: 'from-amber-500 to-orange-500',  badge: 'bg-amber-100 text-amber-800',   icon: '🗄️' },
}

function BenchmarkTable({ stack }) {
  const { t } = useTranslation()

  const METRICS = [
    { key: 'score',          label: t('results.metric_score'),         color: 'blue',   max: 10, format: v => v?.toFixed(2) ?? '—' },
    { key: 'performance',    label: t('results.metric_performance'),   color: 'green',  max: 5 },
    { key: 'learning_curve', label: t('results.metric_learning_curve'), color: 'violet', max: 5 },
    { key: 'maturity',       label: t('results.metric_maturity'),      color: 'blue',   max: 5 },
    { key: 'community',      label: t('results.metric_community'),     color: 'amber',  max: 5 },
    { key: 'difficulty',     label: t('results.metric_difficulty'),    color: 'red',    max: 5 },
  ]

  if (!stack || stack.length === 0) return null

  return (
    <section className="space-y-4">
      <h2 className="text-3xl font-bold text-gray-900">{t('results.benchmark_title')}</h2>
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table className="w-full">
          <thead>
            <tr className="bg-gradient-to-r from-blue-50 to-violet-50 border-b border-gray-200">
              <th className="text-left px-6 py-4 text-sm font-semibold text-gray-600 w-44">{t('results.metric')}</th>
              {stack.map((tech, i) => {
                const style = ROLE_STYLES[tech.role] ?? { badge: 'bg-gray-100 text-gray-700', icon: '🔧' }
                return (
                  <th key={i} className="px-6 py-4 text-center">
                    <div className="font-bold text-gray-900">{tech.name}</div>
                    <span className={`inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full mt-1 ${style.badge}`}>
                      {style.icon} {tech.role ? t('results.role_' + tech.role) : tech.category}
                    </span>
                  </th>
                )
              })}
            </tr>
          </thead>
          <tbody>
            {METRICS.map(({ key, label, color, max, format }, rowIdx) => {
              const values = stack.map(t => t[key] ?? 0)
              const maxVal = Math.max(...values)
              return (
                <tr key={key} className={rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{label}</td>
                  {stack.map((tech, i) => {
                    const val = tech[key] ?? 0
                    const isBest = val === maxVal && maxVal > 0
                    return (
                      <td key={i} className="px-6 py-4">
                        <div className={`rounded-lg p-3 ${isBest ? 'bg-green-50 ring-1 ring-green-200' : ''}`}>
                          <div className="text-center text-sm font-bold text-gray-800 mb-2">
                            {format ? format(val) : val}
                            {isBest && <span className="ml-1 text-green-600 text-xs">{t('results.best')}</span>}
                          </div>
                          <ScoreBar value={val} max={max} color={isBest ? 'green' : color} />
                        </div>
                      </td>
                    )
                  })}
                </tr>
              )
            })}
            <tr className="bg-blue-50 border-t-2 border-blue-200">
              <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('results.use_cases')}</td>
              {stack.map((tech, i) => (
                <td key={i} className="px-6 py-4">
                  <div className="flex flex-wrap gap-1 justify-center">
                    {(tech.use_cases ?? []).slice(0, 3).map(uc => (
                      <span key={uc} className="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">{uc}</span>
                    ))}
                  </div>
                </td>
              ))}
            </tr>
            <tr className="bg-gray-50 border-t border-gray-200">
              <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('results.language')}</td>
              {stack.map((tech, i) => (
                <td key={i} className="px-6 py-4 text-center">
                  <span className="text-xs font-medium bg-gray-200 text-gray-700 px-3 py-1 rounded-full">{tech.language ?? '—'}</span>
                </td>
              ))}
            </tr>
            <tr className="bg-white border-t border-gray-200">
              <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('results.documentation')}</td>
              {stack.map((tech, i) => (
                <td key={i} className="px-6 py-4 text-center">
                  {tech.documentation_url ? (
                    <a href={tech.documentation_url} target="_blank" rel="noopener noreferrer" className="text-xs text-blue-600 hover:underline">
                      {t('results.docs')}
                    </a>
                  ) : '—'}
                </td>
              ))}
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  )
}

export default function Results() {
  const { id } = useParams()
  const { t } = useTranslation()
  const [data, setData] = useState(null)
  const [files, setFiles] = useState(null)
  const [activeFileTab, setActiveFileTab] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [formData, setFormData] = useState({ name: '', email: '', subject: '', message: '' })
  const [submitSuccess, setSubmitSuccess] = useState(false)

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true)
      setError('')
      try {
        const lang = i18n.language || 'fr'
        const [recResponse, filesResponse] = await Promise.all([
          getRecommendation(id, lang),
          getRecommendationFiles(id),
        ])
        setData(recResponse.data)
        setFiles(filesResponse.data)
      } catch (err) {
        setError(t('results.error_load', { message: err.response?.data?.message || err.message }))
      } finally {
        setLoading(false)
      }
    }
    fetchData()

    i18n.on('languageChanged', fetchData)
    return () => i18n.off('languageChanged', fetchData)
  }, [id])

  const handleFormChange = (e) => {
    const { name, value } = e.target
    setFormData({ ...formData, [name]: value })
  }

  const handleAdviceSubmit = async (e) => {
    e.preventDefault()
    setSubmitting(true)
    try {
      await submitAdviceRequest(formData)
      setSubmitSuccess(true)
      setFormData({ name: '', email: '', subject: '', message: '' })
      setTimeout(() => setSubmitSuccess(false), 5000)
    } catch (err) {
      setError(t('results.error_advice', { message: err.response?.data?.message || err.message }))
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center py-20">
        <Loader className="w-12 h-12 text-blue-600 animate-spin mb-4" />
        <p className="text-lg text-gray-600">{t('results.loading')}</p>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-6 text-red-800 flex gap-3">
        <AlertCircle className="w-6 h-6 flex-shrink-0 mt-0.5" />
        <div>
          <h3 className="font-bold mb-2">{t('results.error_title')}</h3>
          <p>{error}</p>
        </div>
      </div>
    )
  }

  if (!data) return null

  const result = data.result ?? {}
  const stack = result.stack ?? []
  const libraries = result.libraries ?? []
  const summary = result.summary ?? ''

  return (
    <div className="space-y-12">
      <div className="bg-gradient-to-r from-blue-600 to-violet-600 text-white rounded-2xl p-12">
        <h1 className="text-4xl font-bold mb-2">{t('results.header_title')}</h1>
        <p className="text-blue-200 text-sm mb-4">{t('results.header_subtitle')}</p>
        {summary && <p className="text-lg text-blue-100">{summary}</p>}
      </div>

      {stack.length > 0 && (
        <section className="space-y-6">
          <h2 className="text-3xl font-bold text-gray-900">{t('results.recommended_stack')}</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {stack.map((tech, idx) => {
              const style = ROLE_STYLES[tech.role] ?? { bg: 'from-gray-400 to-gray-600', badge: 'bg-gray-100 text-gray-700', icon: '🔧' }
              return (
                <div key={idx} className="bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-lg transition overflow-hidden">
                  <div className={`bg-gradient-to-r ${style.bg} p-6`}>
                    <span className="inline-flex items-center gap-1 text-xs font-bold text-white/90 bg-white/20 px-2 py-1 rounded-full mb-2">
                      {style.icon} {tech.role ? t('results.role_' + tech.role) : tech.category}
                    </span>
                    <h3 className="text-2xl font-bold text-white mb-1">{tech.name}</h3>
                    <p className="text-sm text-white/70">{tech.language} · {tech.category}</p>
                  </div>
                  <div className="p-6 space-y-4">
                    <div>
                      <div className="flex items-center justify-between mb-1">
                        <span className="text-sm font-semibold text-gray-700">{t('results.match_score')}</span>
                        <span className="text-lg font-bold text-blue-600">{tech.score?.toFixed(2)}</span>
                      </div>
                      <div className="flex gap-1">
                        {[...Array(5)].map((_, i) => (
                          <Star key={i} className={`w-4 h-4 ${i < Math.round(tech.score ?? 0) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`} />
                        ))}
                      </div>
                    </div>

                    {tech.justification && (
                      <div>
                        <p className="text-xs font-semibold text-gray-500 uppercase mb-1">{t('results.why')}</p>
                        <p className="text-sm text-gray-600">{tech.justification}</p>
                      </div>
                    )}

                    {tech.pros?.length > 0 && (
                      <div>
                        <p className="text-xs font-semibold text-green-700 uppercase mb-1">{t('results.pros')}</p>
                        <ul className="text-sm text-gray-600 space-y-1">
                          {tech.pros.slice(0, 3).map((pro, i) => (
                            <li key={i} className="flex gap-2"><span className="text-green-600 font-bold">+</span>{pro}</li>
                          ))}
                        </ul>
                      </div>
                    )}

                    {tech.cons?.length > 0 && (
                      <div>
                        <p className="text-xs font-semibold text-amber-700 uppercase mb-1">{t('results.cons')}</p>
                        <ul className="text-sm text-gray-600 space-y-1">
                          {tech.cons.slice(0, 2).map((con, i) => (
                            <li key={i} className="flex gap-2"><span className="text-amber-600 font-bold">-</span>{con}</li>
                          ))}
                        </ul>
                      </div>
                    )}
                  </div>
                </div>
              )
            })}
          </div>
        </section>
      )}

      <BenchmarkTable stack={stack} />

      {libraries.length > 0 && (
        <section className="space-y-6">
          <h2 className="text-3xl font-bold text-gray-900">{t('results.libraries')}</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {libraries.map((lib, idx) => (
              <div key={idx} className="bg-white rounded-xl border border-gray-200 p-5 flex gap-4">
                <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">
                  {(lib.name ?? '?')[0]}
                </div>
                <div>
                  <div className="font-semibold text-gray-900">{lib.name}</div>
                  <div className="text-sm text-gray-600 mt-0.5">{lib.purpose}</div>
                  <div className="text-xs text-blue-600 mt-1">{lib.reason}</div>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      <section className="bg-gradient-to-r from-blue-50 to-violet-50 rounded-2xl p-12 border border-blue-200">
        <h2 className="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <Download className="w-6 h-6 text-blue-600" />
          {t('results.download_title')}
        </h2>
        <p className="text-gray-700 mb-6">{t('results.download_desc')}</p>
        <button
          onClick={() => downloadRecommendation(id)}
          className="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition"
        >
          <Download className="w-5 h-5" />
          {t('results.download_button')}
        </button>
      </section>

      {files?.files?.length > 0 && (
        <section className="space-y-6">
          <h2 className="text-3xl font-bold text-gray-900">{t('results.file_preview')}</h2>
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div className="flex border-b border-gray-200 overflow-x-auto bg-gray-50">
              {files.files.map((file, idx) => (
                <button
                  key={idx}
                  onClick={() => setActiveFileTab(idx)}
                  className={`px-4 py-3 font-medium whitespace-nowrap transition ${
                    activeFileTab === idx ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-gray-600 hover:text-gray-900'
                  }`}
                >
                  {file.name}
                </button>
              ))}
            </div>
            <div className="p-8 prose prose-sm max-w-none overflow-auto max-h-96 bg-white">
              <ReactMarkdown>{files.files[activeFileTab]?.content || ''}</ReactMarkdown>
            </div>
          </div>
        </section>
      )}

      <section className="bg-white rounded-2xl border border-gray-200 p-12">
        <h2 className="text-2xl font-bold text-gray-900 mb-2 flex items-center gap-2">
          <Mail className="w-6 h-6 text-violet-600" />
          {t('results.advice_title')}
        </h2>
        <p className="text-gray-600 mb-8">{t('results.advice_desc')}</p>

        {submitSuccess && (
          <div className="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">
            {t('results.advice_success')}
          </div>
        )}

        <form onSubmit={handleAdviceSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="name" placeholder={t('results.advice_name')} value={formData.name} onChange={handleFormChange} required className="p-3 border border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none" />
            <input type="email" name="email" placeholder={t('results.advice_email')} value={formData.email} onChange={handleFormChange} required className="p-3 border border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none" />
          </div>
          <input type="text" name="subject" placeholder={t('results.advice_subject')} value={formData.subject} onChange={handleFormChange} required className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none" />
          <textarea name="message" placeholder={t('results.advice_message')} value={formData.message} onChange={handleFormChange} required rows="4" className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none" />
          <button type="submit" disabled={submitting} className="w-full bg-violet-600 text-white font-semibold py-3 rounded-lg hover:bg-violet-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
            {submitting ? t('results.advice_sending') : t('results.advice_submit')}
          </button>
        </form>
      </section>

      <section className="bg-gradient-to-r from-violet-50 to-blue-50 rounded-2xl p-12 border border-violet-200 text-center">
        <h2 className="text-2xl font-bold text-gray-900 mb-4">{t('results.cta_title')}</h2>
        <p className="text-gray-700 mb-8">{t('results.cta_desc')}</p>
        <Link to="/compare" className="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-blue-700 transition">
          {t('results.cta_button')}
          <ChevronRight className="w-5 h-5" />
        </Link>
      </section>
    </div>
  )
}
