import { getTechnologies } from './api'

const STORAGE_KEY = 'projectadvisor_technologies'
const STORAGE_VERSION = 1
const TTL_MS = 24 * 60 * 60 * 1000 // 24h

function read() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return null
    const parsed = JSON.parse(raw)
    if (parsed.version !== STORAGE_VERSION) return null
    if (Date.now() - parsed.savedAt > TTL_MS) return null
    return parsed.data
  } catch {
    return null
  }
}

function write(data) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      version: STORAGE_VERSION,
      savedAt: Date.now(),
      data,
    }))
  } catch {
    // localStorage full or unavailable
  }
}

export function clearTechCache() {
  localStorage.removeItem(STORAGE_KEY)
}

/**
 * Returns flat array of all technologies, enriched with local overrides.
 * Fetches from API on first call or when cache is stale.
 */
export async function getAllTechnologies(enrichedData = {}) {
  const cached = read()
  if (cached) return applyEnrichment(cached, enrichedData)

  const response = await getTechnologies()
  const flat = (response.data.all || [])
  write(flat)
  return applyEnrichment(flat, enrichedData)
}

function applyEnrichment(techs, enrichedData) {
  if (!enrichedData || Object.keys(enrichedData).length === 0) return techs
  return techs.map(tech => {
    const extra = enrichedData[tech.id]
    return extra ? { ...tech, ...extra } : tech
  })
}

export function getTechById(techs, id) {
  return techs.find(t => t.id === id) ?? null
}
