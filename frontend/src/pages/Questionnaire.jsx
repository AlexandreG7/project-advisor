import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { submitQuestionnaire } from '../services/api'
import i18n from '../i18n'
import { ChevronLeft, ChevronRight, AlertCircle, Check } from 'lucide-react'

const FEATURE_GROUPS = [
  { key: 'users_security', icon: '🔐', features: ['auth', 'roles', 'oauth', 'mfa'] },
  { key: 'data_storage',   icon: '🗄️', features: ['db_relational', 'db_nosql', 'cache', 'file_storage'] },
  { key: 'payments',       icon: '💳', features: ['payment', 'subscription', 'marketplace', 'invoicing'] },
  { key: 'communication',  icon: '📬', features: ['email_transactional', 'notifications_push', 'sms', 'chat'] },
  { key: 'content_media',  icon: '📝', features: ['cms', 'blog', 'media_upload', 'pdf'] },
  { key: 'ai_search',      icon: '🤖', features: ['search', 'ai_llm', 'ai_recommendations'] },
  { key: 'realtime',       icon: '⚡', features: ['websockets', 'collaborative', 'video_streaming'] },
  { key: 'analytics',      icon: '📊', features: ['analytics', 'dashboards', 'seo', 'i18n'] },
  { key: 'devops',         icon: '🔧', features: ['cicd', 'monitoring', 'testing'] },
]

const LANGUAGES = [
  { id: 'JavaScript', abbr: 'JS',  selBg: 'bg-yellow-400' },
  { id: 'TypeScript', abbr: 'TS',  selBg: 'bg-blue-500' },
  { id: 'Python',     abbr: 'PY',  selBg: 'bg-green-500' },
  { id: 'PHP',        abbr: 'PHP', selBg: 'bg-violet-500' },
  { id: 'Go',         abbr: 'GO',  selBg: 'bg-cyan-500' },
  { id: 'Ruby',       abbr: 'RB',  selBg: 'bg-red-500' },
  { id: 'Java',       abbr: 'JV',  selBg: 'bg-orange-500' },
  { id: 'C#',         abbr: 'C#',  selBg: 'bg-indigo-500' },
  { id: 'Rust',       abbr: 'RS',  selBg: 'bg-amber-600' },
  { id: 'Kotlin',     abbr: 'KT',  selBg: 'bg-purple-500' },
  { id: 'Swift',      abbr: 'SW',  selBg: 'bg-rose-500' },
  { id: 'SQL',        abbr: 'SQL', selBg: 'bg-slate-500' },
  { id: 'HTML/CSS',   abbr: 'WEB', selBg: 'bg-pink-500' },
  { id: 'Dart',       abbr: 'DT',  selBg: 'bg-teal-500' },
  { id: 'C/C++',      abbr: 'C++', selBg: 'bg-sky-600' },
  { id: 'Shell',      abbr: 'SH',  selBg: 'bg-gray-600' },
]

export default function Questionnaire() {
  const navigate = useNavigate()
  const { t } = useTranslation()
  const [phase, setPhase] = useState(1)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const [answers, setAnswers] = useState({
    experience: '',
    languages: [],
    goal: '',
    projectType: '',
    scale: '',
    timeline: '',
    features: [],
    hosting: '',
    budget: '',
    teamSize: '',
    convention: 'balanced',
    typescript: true,
    ecosystem: 'mature',
  })

  const set = (key, value) => setAnswers(a => ({ ...a, [key]: value }))
  const toggle = (key, item) =>
    setAnswers(a => ({
      ...a,
      [key]: a[key].includes(item) ? a[key].filter(i => i !== item) : [...a[key], item],
    }))

  const validations = {
    1: answers.experience && answers.languages.length > 0,
    2: answers.goal && answers.projectType && answers.scale && answers.timeline,
    3: true,
    4: answers.hosting && answers.budget && answers.teamSize,
    5: true,
  }

  const handleNext = () => {
    if (!validations[phase]) {
      setError(phase === 1 ? t('questionnaire.error_phase1') : t('questionnaire.error_required'))
      return
    }
    setError('')
    if (phase < 5) setPhase(phase + 1)
  }

  const handlePrevious = () => { setError(''); if (phase > 1) setPhase(phase - 1) }

  const handleSubmit = async () => {
    if (!validations[phase]) { setError(t('questionnaire.error_required')); return }
    setError('')
    setLoading(true)
    try {
      const response = await submitQuestionnaire(answers, i18n.language || 'fr')
      navigate(`/results/${response.data.id}`)
    } catch (err) {
      setError(t('questionnaire.error_api', { message: err.response?.data?.message || err.message }))
      setLoading(false)
    }
  }

  const progressPercent = (phase / 5) * 100
  const PHASES = t('questionnaire.phases', { returnObjects: true })

  return (
    <div className="max-w-4xl mx-auto">
      {/* Progress */}
      <div className="mb-8">
        <div className="flex items-center justify-between mb-3">
          {Array.isArray(PHASES) && PHASES.map((name, i) => (
            <button
              key={i}
              onClick={() => { if (i + 1 < phase) setPhase(i + 1) }}
              className={`text-xs font-bold uppercase tracking-wide transition
                ${i + 1 === phase ? 'text-blue-600' : i + 1 < phase ? 'text-green-600 cursor-pointer' : 'text-gray-400'}`}
            >
              {i + 1 < phase ? <Check className="inline w-3.5 h-3.5 mr-0.5" /> : null}
              {i + 1}. {name}
            </button>
          ))}
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div
            className="bg-gradient-to-r from-blue-600 to-violet-600 h-2 rounded-full transition-all duration-300"
            style={{ width: `${progressPercent}%` }}
          />
        </div>
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex gap-3 text-red-800">
          <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5" />
          <p>{error}</p>
        </div>
      )}

      <div className="bg-white rounded-2xl shadow-lg p-10 mb-8">
        {/* PHASE 1 */}
        {phase === 1 && (
          <div className="space-y-8">
            <div>
              <h3 className="text-2xl font-bold text-gray-900 mb-1">{t('questionnaire.phase1.title')}</h3>
              <p className="text-gray-600">{t('questionnaire.phase1.subtitle')}</p>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase1.experience_label')}</label>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {[
                  { value: 'beginner',     label: t('questionnaire.phase1.beginner'),     desc: t('questionnaire.phase1.beginner_desc') },
                  { value: 'intermediate', label: t('questionnaire.phase1.intermediate'), desc: t('questionnaire.phase1.intermediate_desc') },
                  { value: 'advanced',     label: t('questionnaire.phase1.advanced'),     desc: t('questionnaire.phase1.advanced_desc') },
                ].map(opt => (
                  <button
                    key={opt.value}
                    onClick={() => set('experience', opt.value)}
                    className={`p-4 rounded-xl border-2 transition text-center ${
                      answers.experience === opt.value ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900">{opt.label}</div>
                    <div className="text-sm text-gray-500">{opt.desc}</div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">{t('questionnaire.phase1.languages_label')}</label>
              <p className="text-xs text-gray-500 mb-4">{t('questionnaire.phase1.languages_hint')}</p>
              <div className="flex flex-wrap gap-2">
                {LANGUAGES.map(lang => {
                  const sel = answers.languages.includes(lang.id)
                  return (
                    <button
                      key={lang.id}
                      type="button"
                      onClick={() => toggle('languages', lang.id)}
                      className={`flex items-center gap-2 px-3 py-2 rounded-lg border-2 font-medium text-sm transition-all select-none
                        ${sel ? `${lang.selBg} text-white border-transparent shadow-md scale-105` : 'bg-gray-50 border-gray-200 text-gray-700 hover:scale-105'}`}
                    >
                      <span className={`text-xs font-bold px-1.5 py-0.5 rounded ${sel ? 'bg-white/20' : 'bg-white/60'}`}>
                        {lang.abbr}
                      </span>
                      {lang.id}
                    </button>
                  )
                })}
              </div>
              {answers.languages.length > 0 && (
                <p className="text-xs text-blue-600 mt-3 font-medium">
                  {t(`questionnaire.phase1.languages_selected`, {
                    count: answers.languages.length,
                    list: answers.languages.join(', '),
                  })}
                </p>
              )}
            </div>
          </div>
        )}

        {/* PHASE 2 */}
        {phase === 2 && (
          <div className="space-y-8">
            <div>
              <h3 className="text-2xl font-bold text-gray-900 mb-1">{t('questionnaire.phase2.title')}</h3>
              <p className="text-gray-600">{t('questionnaire.phase2.subtitle')}</p>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase2.goal_label')}</label>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {[
                  { value: 'learn',       icon: '📚' },
                  { value: 'mvp',         icon: '🚀' },
                  { value: 'performance', icon: '⚡' },
                  { value: 'portfolio',   icon: '🎨' },
                  { value: 'client',      icon: '💼' },
                  { value: 'enterprise',  icon: '🏢' },
                ].map(opt => (
                  <button
                    key={opt.value}
                    onClick={() => set('goal', opt.value)}
                    className={`p-4 rounded-xl border-2 transition text-left flex gap-3 items-start ${
                      answers.goal === opt.value ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <span className="text-2xl">{opt.icon}</span>
                    <div>
                      <div className="font-semibold text-gray-900">{t(`questionnaire.phase2.goal_${opt.value}`)}</div>
                      <div className="text-sm text-gray-500">{t(`questionnaire.phase2.goal_${opt.value}_desc`)}</div>
                    </div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase2.project_type_label')}</label>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                {[
                  { value: 'saas',        icon: '📊' },
                  { value: 'ecommerce',   icon: '🛒' },
                  { value: 'blog',        icon: '✍️' },
                  { value: 'api',         icon: '🔌' },
                  { value: 'branding',    icon: '🌐' },
                  { value: 'marketplace', icon: '🏪' },
                  { value: 'social',      icon: '👥' },
                  { value: 'internal',    icon: '🏗️' },
                  { value: 'mobile',      icon: '📱' },
                ].map(opt => (
                  <button
                    key={opt.value}
                    onClick={() => set('projectType', opt.value)}
                    className={`p-3 rounded-xl border-2 transition text-center ${
                      answers.projectType === opt.value ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="text-xl mb-1">{opt.icon}</div>
                    <div className="text-sm font-semibold text-gray-900">{t(`questionnaire.phase2.type_${opt.value}`)}</div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase2.scale_label')}</label>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                {['tiny', 'small', 'medium', 'large'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('scale', v)}
                    className={`p-3 rounded-xl border-2 transition text-center ${
                      answers.scale === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-bold text-gray-900">{t(`questionnaire.phase2.scale_${v}`)}</div>
                    <div className="text-xs text-gray-500">{t(`questionnaire.phase2.scale_${v}_desc`)}</div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase2.timeline_label')}</label>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                {['asap', 'short', 'medium', 'relaxed'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('timeline', v)}
                    className={`p-3 rounded-xl border-2 transition text-center ${
                      answers.timeline === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900">{t(`questionnaire.phase2.timeline_${v}`)}</div>
                    <div className="text-xs text-gray-500">{t(`questionnaire.phase2.timeline_${v}_desc`)}</div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* PHASE 3 */}
        {phase === 3 && (
          <div className="space-y-6">
            <div>
              <h3 className="text-2xl font-bold text-gray-900 mb-1">{t('questionnaire.phase3.title')}</h3>
              <p className="text-gray-600">{t('questionnaire.phase3.subtitle')}</p>
              {answers.features.length > 0 && (
                <p className="text-sm text-blue-600 font-medium mt-2">
                  {t('questionnaire.phase3.selected', { count: answers.features.length })}
                </p>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {FEATURE_GROUPS.map(group => (
                <div key={group.key} className="bg-gray-50 rounded-xl p-4 border border-gray-100">
                  <h4 className="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span className="text-lg">{group.icon}</span>
                    {t(`questionnaire.phase3.group_${group.key}`)}
                  </h4>
                  <div className="space-y-1.5">
                    {group.features.map(fKey => {
                      const checked = answers.features.includes(fKey)
                      return (
                        <label
                          key={fKey}
                          className={`flex items-center gap-3 p-2 rounded-lg cursor-pointer transition
                            ${checked ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-white'}`}
                        >
                          <div className={`w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition
                            ${checked ? 'bg-blue-600 border-blue-600' : 'border-gray-300 bg-white'}`}
                          >
                            {checked && <Check className="w-3.5 h-3.5 text-white" />}
                          </div>
                          <input type="checkbox" checked={checked} onChange={() => toggle('features', fKey)} className="hidden" />
                          <span className={`text-sm ${checked ? 'font-semibold text-gray-900' : 'text-gray-700'}`}>
                            {t(`questionnaire.phase3.feat_${fKey}`)}
                          </span>
                        </label>
                      )
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* PHASE 4 */}
        {phase === 4 && (
          <div className="space-y-8">
            <div>
              <h3 className="text-2xl font-bold text-gray-900 mb-1">{t('questionnaire.phase4.title')}</h3>
              <p className="text-gray-600">{t('questionnaire.phase4.subtitle')}</p>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase4.hosting_label')}</label>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                {['vercel', 'aws', 'vps', 'none'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('hosting', v)}
                    className={`p-3 rounded-xl border-2 transition text-center ${
                      answers.hosting === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900 text-sm">{t(`questionnaire.phase4.hosting_${v}`)}</div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase4.budget_label')}</label>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {['free', 'small', 'unlimited'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('budget', v)}
                    className={`p-4 rounded-xl border-2 transition text-center ${
                      answers.budget === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900">{t(`questionnaire.phase4.budget_${v}`)}</div>
                    <div className="text-sm text-gray-500">{t(`questionnaire.phase4.budget_${v}_desc`)}</div>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase4.team_label')}</label>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {['solo', 'small', 'large'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('teamSize', v)}
                    className={`p-4 rounded-xl border-2 transition text-center ${
                      answers.teamSize === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900">{t(`questionnaire.phase4.team_${v}`)}</div>
                    <div className="text-sm text-gray-500">{t(`questionnaire.phase4.team_${v}_desc`)}</div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* PHASE 5 */}
        {phase === 5 && (
          <div className="space-y-8">
            <div>
              <h3 className="text-2xl font-bold text-gray-900 mb-1">{t('questionnaire.phase5.title')}</h3>
              <p className="text-gray-600">{t('questionnaire.phase5.subtitle')}</p>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase5.convention_label')}</label>
              <div className="flex flex-wrap gap-4">
                {['convention', 'balanced', 'flexibility'].map(v => (
                  <label key={v} className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="convention"
                      value={v}
                      checked={answers.convention === v}
                      onChange={() => set('convention', v)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span className="font-medium text-gray-900">{t(`questionnaire.phase5.convention_${v}`)}</span>
                  </label>
                ))}
              </div>
            </div>

            <div>
              <label className="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={answers.typescript}
                  onChange={() => set('typescript', !answers.typescript)}
                  className="w-4 h-4 text-blue-600 rounded"
                />
                <span className="font-semibold text-gray-900">{t('questionnaire.phase5.typescript_label')}</span>
              </label>
              <p className="text-sm text-gray-500 mt-1 ml-7">{t('questionnaire.phase5.typescript_hint')}</p>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-4">{t('questionnaire.phase5.maturity_label')}</label>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {['stable', 'mature', 'emerging'].map(v => (
                  <button
                    key={v}
                    onClick={() => set('ecosystem', v)}
                    className={`p-4 rounded-xl border-2 transition text-center ${
                      answers.ecosystem === v ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <div className="font-semibold text-gray-900">{t(`questionnaire.phase5.maturity_${v}`)}</div>
                    <div className="text-sm text-gray-500">{t(`questionnaire.phase5.maturity_${v}_desc`)}</div>
                  </button>
                ))}
              </div>
            </div>

            <div className="bg-gradient-to-r from-blue-50 to-violet-50 rounded-xl p-6 border border-blue-200">
              <h4 className="font-bold text-gray-900 mb-3">{t('questionnaire.phase5.summary_title')}</h4>
              <div className="grid grid-cols-2 gap-2 text-sm text-gray-700">
                <div>{t('questionnaire.phase5.summary_level')} <span className="font-semibold">{answers.experience || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_languages')} <span className="font-semibold">{answers.languages.join(', ') || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_goal')} <span className="font-semibold">{answers.goal || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_type')} <span className="font-semibold">{answers.projectType || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_scale')} <span className="font-semibold">{answers.scale || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_timeline')} <span className="font-semibold">{answers.timeline || '—'}</span></div>
                <div>{t('questionnaire.phase5.summary_features')} <span className="font-semibold">{t('questionnaire.phase5.summary_features_count', { count: answers.features.length })}</span></div>
                <div>{t('questionnaire.phase5.summary_team')} <span className="font-semibold">{answers.teamSize || '—'}</span></div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Navigation */}
      <div className="flex gap-4 justify-between">
        <button
          onClick={handlePrevious}
          disabled={phase === 1}
          className="flex items-center gap-2 px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-900 font-semibold hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <ChevronLeft className="w-5 h-5" /> {t('questionnaire.nav_previous')}
        </button>

        <div className="flex gap-4">
          {phase < 5 ? (
            <button
              onClick={handleNext}
              className="flex items-center gap-2 px-8 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-violet-600 text-white font-semibold hover:shadow-lg transition"
            >
              {t('questionnaire.nav_next')} <ChevronRight className="w-5 h-5" />
            </button>
          ) : (
            <button
              onClick={handleSubmit}
              disabled={loading}
              className="px-8 py-3 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? t('questionnaire.analyzing') : t('questionnaire.get_recommendations')}
            </button>
          )}
        </div>
      </div>
    </div>
  )
}
