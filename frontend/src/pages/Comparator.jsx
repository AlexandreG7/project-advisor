import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { compareStacks } from '../services/api'
import { getAllTechnologies, clearTechCache } from '../services/techStorage'
import { TECH_ENRICHED_DATA } from '../data/techEnriched'
import { X, Loader, AlertCircle, RefreshCw } from 'lucide-react'

function ScoreBar({ value, max = 5, color = 'blue' }) {
  const colors = {
    blue: 'bg-blue-500', green: 'bg-green-500', amber: 'bg-amber-500',
    violet: 'bg-violet-500', red: 'bg-red-400',
  }
  return (
    <div className="flex items-center gap-2">
      <div className="flex-1 bg-gray-100 rounded-full h-2">
        <div
          className={`${colors[color]} h-2 rounded-full transition-all`}
          style={{ width: `${Math.round((value / max) * 100)}%` }}
        />
      </div>
      <span className="text-xs font-semibold text-gray-600 w-4 text-right">{value}</span>
    </div>
  )
}

export default function Comparator() {
  const { t } = useTranslation()
  const [technologies, setTechnologies] = useState([])
  const [selectedTechs, setSelectedTechs] = useState([])
  const [loadingTechs, setLoadingTechs] = useState(true)
  const [error, setError] = useState('')
  const [input, setInput] = useState('')

  const METRICS = [
    { key: 'performance',    label: t('comparator.metric_performance'),    color: 'green',  max: 5 },
    { key: 'learning_curve', label: t('comparator.metric_learning_curve'), color: 'violet', max: 5 },
    { key: 'ecosystem',      label: t('comparator.metric_ecosystem'),      color: 'blue',   max: 5 },
    { key: 'maturity',       label: t('comparator.metric_maturity'),       color: 'blue',   max: 5 },
    { key: 'community',      label: t('comparator.metric_community'),      color: 'amber',  max: 5 },
    { key: 'difficulty',     label: t('comparator.metric_difficulty'),     color: 'red',    max: 5 },
  ]

  useEffect(() => {
    getAllTechnologies(TECH_ENRICHED_DATA)
      .then(setTechnologies)
      .catch(err => setError('Failed to load technologies: ' + err.message))
      .finally(() => setLoadingTechs(false))
  }, [])

  const handleRefreshCache = async () => {
    clearTechCache()
    setLoadingTechs(true)
    setError('')
    try {
      const techs = await getAllTechnologies(TECH_ENRICHED_DATA)
      setTechnologies(techs)
    } catch (err) {
      setError('Failed to refresh: ' + err.message)
    } finally {
      setLoadingTechs(false)
    }
  }

  const addTechnology = (tech) => {
    if (selectedTechs.length < 3 && !selectedTechs.find(t => t.id === tech.id)) {
      setSelectedTechs([...selectedTechs, tech])
      setInput('')
    }
  }

  const removeTechnology = (techId) => {
    setSelectedTechs(selectedTechs.filter(t => t.id !== techId))
  }

  const filteredTechs = input.trim()
    ? technologies.filter(t =>
        t.name?.toLowerCase().includes(input.toLowerCase()) ||
        t.category?.toLowerCase().includes(input.toLowerCase())
      )
    : []

  return (
    <div className="space-y-12">
      <div className="bg-gradient-to-r from-blue-600 to-violet-600 text-white rounded-2xl p-12">
        <div className="flex items-start justify-between">
          <div>
            <h1 className="text-4xl font-bold mb-4">{t('comparator.title')}</h1>
            <p className="text-xl text-blue-100">{t('comparator.subtitle')}</p>
          </div>
          <button
            onClick={handleRefreshCache}
            title={t('comparator.refresh')}
            className="flex items-center gap-2 text-blue-200 hover:text-white transition text-sm mt-1"
          >
            <RefreshCw className="w-4 h-4" />
            {t('comparator.refresh')}
          </button>
        </div>
      </div>

      <section>
        <div className="bg-white rounded-xl border border-gray-200 p-8">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h2 className="text-xl font-bold text-gray-900">{t('comparator.select_title')}</h2>
              <p className="text-gray-500 text-sm mt-1">
                {loadingTechs
                  ? t('comparator.loading_techs')
                  : t('comparator.count', { total: technologies.length, selected: selectedTechs.length })}
              </p>
            </div>
          </div>

          <div className="relative mb-6">
            <input
              type="text"
              placeholder={loadingTechs ? t('comparator.search_loading') : t('comparator.search_placeholder')}
              value={input}
              onChange={e => setInput(e.target.value)}
              disabled={loadingTechs}
              className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-600 focus:outline-none disabled:bg-gray-50"
            />
            {input && filteredTechs.length > 0 && (
              <div className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-64 overflow-y-auto">
                {filteredTechs.map(tech => (
                  <button
                    key={tech.id}
                    onClick={() => addTechnology(tech)}
                    disabled={selectedTechs.length >= 3 || selectedTechs.some(t => t.id === tech.id)}
                    className="w-full text-left px-4 py-3 hover:bg-blue-50 transition disabled:opacity-40 disabled:cursor-not-allowed border-b border-gray-100 last:border-b-0"
                  >
                    <div className="font-semibold text-gray-900">{tech.name}</div>
                    <div className="text-xs text-gray-500">{tech.category}</div>
                  </button>
                ))}
              </div>
            )}
            {input && filteredTechs.length === 0 && !loadingTechs && (
              <div className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow p-4 text-sm text-gray-500 z-10">
                {t('comparator.no_match', { input })}
              </div>
            )}
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {selectedTechs.map(tech => (
              <div key={tech.id} className="bg-blue-50 border-2 border-blue-500 rounded-lg p-4 flex items-start justify-between">
                <div>
                  <div className="font-bold text-gray-900">{tech.name}</div>
                  <div className="text-xs text-gray-500 mt-0.5">{tech.category}</div>
                </div>
                <button onClick={() => removeTechnology(tech.id)} className="text-gray-400 hover:text-red-500 transition ml-2">
                  <X className="w-4 h-4" />
                </button>
              </div>
            ))}
            {[...Array(3 - selectedTechs.length)].map((_, i) => (
              <div key={i} className="border-2 border-dashed border-gray-200 rounded-lg p-4 flex items-center justify-center text-gray-400 text-sm">
                {t('comparator.add_slot')}
              </div>
            ))}
          </div>

          {error && (
            <div className="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 flex gap-3 text-red-800 text-sm">
              <AlertCircle className="w-4 h-4 flex-shrink-0 mt-0.5" />
              <p>{error}</p>
            </div>
          )}
        </div>
      </section>

      {selectedTechs.length >= 2 && (
        <section className="space-y-4">
          <h2 className="text-3xl font-bold text-gray-900">{t('comparator.comparison_title')}</h2>

          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table className="w-full">
              <thead>
                <tr className="bg-gradient-to-r from-blue-50 to-violet-50 border-b border-gray-200">
                  <th className="text-left px-6 py-4 text-sm font-semibold text-gray-600 w-44">{t('comparator.col_metric')}</th>
                  {selectedTechs.map(tech => (
                    <th key={tech.id} className="px-6 py-4 text-center">
                      <div className="font-bold text-gray-900">{tech.name}</div>
                      <div className="text-xs text-gray-500 font-normal mt-0.5">{tech.category}</div>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {METRICS.map(({ key, label, color, max }, rowIdx) => {
                  const values = selectedTechs.map(t => t[key] ?? 0)
                  const maxVal = Math.max(...values)
                  return (
                    <tr key={key} className={rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                      <td className="px-6 py-4 text-sm font-semibold text-gray-700">{label}</td>
                      {selectedTechs.map(tech => {
                        const val = tech[key] ?? 0
                        const isBest = val === maxVal && maxVal > 0
                        return (
                          <td key={tech.id} className="px-6 py-4">
                            <div className={`rounded-lg p-3 ${isBest ? 'bg-green-50 ring-1 ring-green-200' : ''}`}>
                              <div className="text-center text-sm font-bold text-gray-800 mb-2">
                                {val}
                                {isBest && <span className="ml-1 text-green-600 text-xs">✓</span>}
                              </div>
                              <ScoreBar value={val} max={max} color={isBest ? 'green' : color} />
                            </div>
                          </td>
                        )
                      })}
                    </tr>
                  )
                })}

                <tr className="bg-blue-50 border-t-2 border-blue-100">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('comparator.col_language')}</td>
                  {selectedTechs.map(tech => (
                    <td key={tech.id} className="px-6 py-4 text-center">
                      <span className="text-xs bg-gray-200 text-gray-700 px-3 py-1 rounded-full font-medium">{tech.language ?? '—'}</span>
                    </td>
                  ))}
                </tr>

                <tr className="bg-white border-t border-gray-100">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('comparator.col_use_cases')}</td>
                  {selectedTechs.map(tech => (
                    <td key={tech.id} className="px-6 py-4">
                      <div className="flex flex-wrap gap-1 justify-center">
                        {(tech.use_cases ?? []).slice(0, 3).map(uc => (
                          <span key={uc} className="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">{uc}</span>
                        ))}
                      </div>
                    </td>
                  ))}
                </tr>

                <tr className="bg-gray-50 border-t border-gray-100">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('comparator.col_weekly_dl')}</td>
                  {selectedTechs.map(tech => (
                    <td key={tech.id} className="px-6 py-4 text-center text-sm text-gray-600">{tech.weekly_downloads ?? '—'}</td>
                  ))}
                </tr>

                <tr className="bg-white border-t border-gray-100">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('comparator.col_github_stars')}</td>
                  {selectedTechs.map(tech => (
                    <td key={tech.id} className="px-6 py-4 text-center text-sm text-gray-600">{tech.github_stars ?? '—'}</td>
                  ))}
                </tr>

                <tr className="bg-gray-50 border-t border-gray-100">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-700">{t('comparator.col_docs')}</td>
                  {selectedTechs.map(tech => (
                    <td key={tech.id} className="px-6 py-4 text-center">
                      {tech.documentation_url ? (
                        <a href={tech.documentation_url} target="_blank" rel="noopener noreferrer" className="text-xs text-blue-600 hover:underline">
                          Docs →
                        </a>
                      ) : '—'}
                    </td>
                  ))}
                </tr>
              </tbody>
            </table>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {selectedTechs.map(tech => (
              <div key={tech.id} className="bg-white border border-gray-200 rounded-xl p-6">
                <h3 className="text-lg font-bold text-gray-900 mb-1">{tech.name}</h3>
                <p className="text-xs text-gray-500 mb-3">{tech.category}</p>
                {tech.description && <p className="text-sm text-gray-600 mb-4">{tech.description}</p>}
                {tech.pros?.length > 0 && (
                  <div className="mb-3">
                    <p className="text-xs font-semibold text-green-700 uppercase mb-1">Pros</p>
                    <ul className="space-y-1">
                      {tech.pros.slice(0, 3).map((p, i) => (
                        <li key={i} className="text-xs text-gray-600 flex gap-1.5"><span className="text-green-500 font-bold">+</span>{p}</li>
                      ))}
                    </ul>
                  </div>
                )}
                {tech.cons?.length > 0 && (
                  <div>
                    <p className="text-xs font-semibold text-amber-700 uppercase mb-1">Cons</p>
                    <ul className="space-y-1">
                      {tech.cons.slice(0, 2).map((c, i) => (
                        <li key={i} className="text-xs text-gray-600 flex gap-1.5"><span className="text-amber-500 font-bold">-</span>{c}</li>
                      ))}
                    </ul>
                  </div>
                )}
              </div>
            ))}
          </div>
        </section>
      )}

      {selectedTechs.length === 1 && (
        <div className="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center text-blue-700">
          {t('comparator.add_more')}
        </div>
      )}

      {selectedTechs.length === 0 && !loadingTechs && (
        <div className="bg-gradient-to-r from-blue-50 to-violet-50 rounded-2xl p-12 border border-blue-200 text-center">
          <h3 className="text-2xl font-bold text-gray-900 mb-4">{t('comparator.how_to_title')}</h3>
          <p className="text-gray-600 max-w-xl mx-auto">{t('comparator.how_to_desc')}</p>
        </div>
      )}
    </div>
  )
}
