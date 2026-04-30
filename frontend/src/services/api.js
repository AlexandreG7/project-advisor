import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { 'Content-Type': 'application/json' }
})

export const submitQuestionnaire = (answers, lang = 'fr') => api.post('/recommendations', { ...answers, lang })
export const getRecommendation = (id, lang = 'fr') => api.get(`/recommendations/${id}?lang=${lang}`)
export const getRecommendationFiles = (id) => api.get(`/recommendations/${id}/files`)
export const compareStacks = (techIds) => api.post('/compare', { ids: techIds })
export const getTechnologies = () => api.get('/technologies')
export const submitAdviceRequest = (data) => api.post('/advice-requests', data)
export const downloadRecommendation = (id) => {
  window.location.href = `/api/recommendations/${id}/download`
}

export default api
