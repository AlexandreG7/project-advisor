import { Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import Questionnaire from './pages/Questionnaire'
import Results from './pages/Results'
import Comparator from './pages/Comparator'
import Layout from './components/Layout'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Layout />}>
        <Route index element={<Home />} />
        <Route path="questionnaire" element={<Questionnaire />} />
        <Route path="results/:id" element={<Results />} />
        <Route path="compare" element={<Comparator />} />
      </Route>
    </Routes>
  )
}
