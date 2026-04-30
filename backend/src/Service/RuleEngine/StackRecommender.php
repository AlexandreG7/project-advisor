<?php

namespace App\Service\RuleEngine;

use Symfony\Component\Filesystem\Filesystem;

class StackRecommender
{
    private array $technologies = [];
    private Filesystem $filesystem;

    public function __construct(
        private string $projectDir
    ) {
        $this->filesystem = new Filesystem();
        $this->loadTechnologies();
    }

    private function loadTechnologies(): void
    {
        $techFile = $this->projectDir . '/data/knowledge/technologies.json';
        if (!$this->filesystem->exists($techFile)) {
            $this->technologies = [];
            return;
        }

        $content = file_get_contents($techFile);
        $data = json_decode($content, true);
        $this->technologies = $data['technologies'] ?? [];
    }

    /**
     * Normalize raw questionnaire answers (frontend field names → internal names)
     */
    private function normalizeAnswers(array $raw): array
    {
        // experience → profile
        $profile = $raw['profile'] ?? $raw['experience'] ?? 'intermediate';

        // goal → objective (with value mapping)
        $goalMap = [
            'learn'       => 'learning',
            'learning'    => 'learning',
            'mvp'         => 'mvp',
            'performance' => 'performance',
            'portfolio'   => 'balanced',
            'client'      => 'balanced',
        ];
        $rawGoal = $raw['objective'] ?? $raw['goal'] ?? 'balanced';
        $objective = $goalMap[$rawGoal] ?? 'balanced';

        // projectType → useCases
        $typeToUseCases = [
            'saas'      => ['saas', 'dashboard'],
            'ecommerce' => ['ecommerce', 'saas'],
            'blog'      => ['blog', 'landing'],
            'api'       => ['api'],
            'branding'  => ['landing', 'portfolio'],
            'hybrid'    => ['mobile', 'saas'],
        ];
        $useCases = $raw['useCases']
            ?? $typeToUseCases[$raw['projectType'] ?? '']
            ?? [];

        // known languages (lowercase for comparison)
        $knownLanguages = array_map(
            'strtolower',
            $raw['languages'] ?? []
        );

        return [
            'objective'      => $objective,
            'profile'        => $profile,
            'useCases'       => $useCases,
            'features'       => $raw['features'] ?? [],
            'constraints'    => $raw['constraints'] ?? [],
            'knownLanguages' => $knownLanguages,
            'rawGoal'        => $rawGoal,
        ];
    }

    /**
     * Generate a stack recommendation based on answers
     */
    public function recommend(array $rawAnswers, string $lang = 'fr'): array
    {
        $answers = $this->normalizeAnswers($rawAnswers);

        $objective      = $answers['objective'];
        $profile        = $answers['profile'];
        $useCases       = $answers['useCases'];
        $features       = $answers['features'];
        $constraints    = $answers['constraints'];
        $knownLanguages = $answers['knownLanguages'];

        // Calculate weight multipliers based on objective
        $weights = $this->calculateWeights($objective);

        // Filter compatible technologies
        $compatible = $this->filterCompatible($useCases, $constraints);

        // Score each technology
        $scored = array_map(function ($tech) use ($weights, $useCases, $features, $profile, $objective, $knownLanguages, $lang) {
            return $this->scoreTechnology($tech, $weights, $useCases, $features, $profile, $objective, $knownLanguages, $lang);
        }, $compatible);

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Build a balanced stack: one best tech per role (Frontend, Backend, Database)
        $topRecommendations = $this->buildBalancedStack(array_values($scored));

        // Get complementary libraries
        $libraries = $this->getComplementaryLibraries($topRecommendations, $features, $lang);

        // Generate summary
        $summary = $this->generateSummary($topRecommendations, $objective, $profile, $lang);

        return [
            'stack' => $topRecommendations,
            'libraries' => $libraries,
            'summary' => $summary,
            'metadata' => [
                'objective'      => $objective,
                'profile'        => $profile,
                'useCases'       => $useCases,
                'knownLanguages' => $knownLanguages,
                'timestamp'      => date('c'),
            ],
        ];
    }

    /**
     * Build a coherent stack by selecting the best technology per role.
     * Roles: Fullstack (or Frontend + Backend separately), Database.
     */
    private function buildBalancedStack(array $scored): array
    {
        $bestByCategory = [];

        foreach ($scored as $tech) {
            $cat = $tech['category'] ?? 'other';
            if (!isset($bestByCategory[$cat])) {
                $bestByCategory[$cat] = $tech;
            }
        }

        $stack = [];

        // Determine frontend/backend strategy
        $bestFullstack = $bestByCategory['framework-fullstack'] ?? null;
        $bestFrontend  = $bestByCategory['framework-frontend']  ?? null;
        $bestBackend   = $bestByCategory['framework-backend']   ?? null;
        $bestDatabase  = $bestByCategory['database']            ?? null;

        // If a fullstack framework exists and scores better than combining
        // frontend + backend separately, use it
        if ($bestFullstack && $bestFrontend && $bestBackend) {
            $fullstackScore = $bestFullstack['score'];
            $separateScore  = ($bestFrontend['score'] + $bestBackend['score']) / 2;
            if ($fullstackScore >= $separateScore) {
                $stack[] = array_merge($bestFullstack, ['role' => 'fullstack']);
            } else {
                $stack[] = array_merge($bestFrontend, ['role' => 'frontend']);
                $stack[] = array_merge($bestBackend,  ['role' => 'backend']);
            }
        } elseif ($bestFullstack) {
            $stack[] = array_merge($bestFullstack, ['role' => 'fullstack']);
        } else {
            if ($bestFrontend) $stack[] = array_merge($bestFrontend, ['role' => 'frontend']);
            if ($bestBackend)  $stack[] = array_merge($bestBackend,  ['role' => 'backend']);
        }

        // Always add best database
        if ($bestDatabase) {
            $stack[] = array_merge($bestDatabase, ['role' => 'database']);
        }

        return $stack;
    }

    private function calculateWeights(string $objective): array
    {
        return match ($objective) {
            'learning' => [
                'learning_curve' => 0.35,
                'ecosystem'      => 0.20,
                'community'      => 0.20,
                'performance'    => 0.15,
                'maturity'       => 0.10,
            ],
            'mvp' => [
                'ecosystem'      => 0.30,
                'learning_curve' => 0.20,
                'community'      => 0.20,
                'performance'    => 0.15,
                'maturity'       => 0.15,
            ],
            'performance' => [
                'performance'    => 0.40,
                'ecosystem'      => 0.25,
                'maturity'       => 0.15,
                'community'      => 0.10,
                'learning_curve' => 0.10,
            ],
            default => [
                'ecosystem'      => 0.25,
                'performance'    => 0.25,
                'community'      => 0.20,
                'maturity'       => 0.15,
                'learning_curve' => 0.15,
            ],
        };
    }

    private function filterCompatible(array $useCases, array $constraints): array
    {
        return array_filter($this->technologies, function ($tech) use ($useCases, $constraints) {
            // Check if tech supports at least one use case
            $techUseCases = $tech['use_cases'] ?? [];
            $hasUseCase = empty($useCases) || !empty(array_intersect($useCases, $techUseCases));

            // Check constraints
            if (in_array('no-database', $constraints) && in_array('database', (array)$tech['type'])) {
                return false;
            }

            if (in_array('no-backend', $constraints) && in_array('backend', (array)$tech['type'])) {
                return false;
            }

            return $hasUseCase;
        });
    }

    private function scoreTechnology(
        array $tech,
        array $weights,
        array $useCases,
        array $features,
        string $profile,
        string $objective = 'balanced',
        array $knownLanguages = [],
        string $lang = 'fr'
    ): array {
        $score = 0;

        // Learning curve scoring (inverse - lower difficulty = better for beginners)
        if ($profile === 'beginner') {
            $score += (6 - ($tech['learning_curve'] ?? 3)) * $weights['learning_curve'];
        } else {
            $score += ($tech['learning_curve'] ?? 3) * $weights['learning_curve'];
        }

        // Ecosystem score
        $score += ($tech['ecosystem'] ?? 3) * $weights['ecosystem'];

        // Performance score
        $score += ($tech['performance'] ?? 3) * $weights['performance'];

        // Community score
        $score += ($tech['community'] ?? 3) * $weights['community'];

        // Maturity score
        $score += ($tech['maturity'] ?? 3) * $weights['maturity'];

        // Known language bonus
        if (!empty($knownLanguages)) {
            $techLang = strtolower($tech['language'] ?? '');

            // Map tech languages to the user's language list
            // e.g. "javascript" matches JS or TS, "sql" matches SQL, etc.
            $langAliases = [
                'javascript' => ['javascript', 'js', 'typescript', 'html/css'],
                'typescript' => ['typescript', 'ts', 'javascript', 'js'],
                'python'     => ['python', 'py'],
                'php'        => ['php'],
                'go'         => ['go'],
                'ruby'       => ['ruby', 'rb'],
                'java'       => ['java', 'kotlin'],
                'sql'        => ['sql'],
            ];

            $aliases = $langAliases[$techLang] ?? [$techLang];
            $languageKnown = !empty(array_intersect($knownLanguages, $aliases));

            if ($languageKnown) {
                if ($objective === 'learning') {
                    // Strong bonus: user learns in a familiar language
                    $score += 2.0;
                } else {
                    // Soft preference: slight boost for known language
                    $score += 0.5;
                }
            } elseif ($objective === 'learning') {
                // In learning mode, slightly penalize unknown languages
                $score -= 0.5;
            }
        }

        // Use case match bonus
        $techUseCases = $tech['use_cases'] ?? [];
        $matchingUseCases = array_intersect($useCases, $techUseCases);
        $score += count($matchingUseCases) * 0.5;

        // Integration match bonus
        $techIntegrations = $tech['integrations'] ?? [];
        $matchingIntegrations = array_intersect($features, $techIntegrations);
        $score += count($matchingIntegrations) * 0.3;

        // Apply English text fields when lang is 'en'
        if ($lang === 'en') {
            if (!empty($tech['description_en'])) $tech['description'] = $tech['description_en'];
            if (!empty($tech['pros_en']))         $tech['pros']        = $tech['pros_en'];
            if (!empty($tech['cons_en']))         $tech['cons']        = $tech['cons_en'];
        }
        unset($tech['description_en'], $tech['pros_en'], $tech['cons_en']);

        $tech['score'] = round($score, 2);
        $tech['justification'] = $this->generateJustification($tech, $matchingUseCases, $matchingIntegrations, $knownLanguages, $objective, $lang);

        return $tech;
    }

    private function generateJustification(
        array $tech,
        array $matchingUseCases,
        array $matchingIntegrations,
        array $knownLanguages = [],
        string $objective = 'balanced',
        string $lang = 'fr'
    ): string {
        $reasons = [];
        $isEn = $lang === 'en';

        // Language match
        if (!empty($knownLanguages)) {
            $techLang = strtolower($tech['language'] ?? '');
            $langAliases = [
                'javascript' => ['javascript', 'js', 'typescript', 'html/css'],
                'typescript' => ['typescript', 'ts', 'javascript', 'js'],
                'python'     => ['python', 'py'],
                'php'        => ['php'],
                'go'         => ['go'],
                'ruby'       => ['ruby', 'rb'],
                'java'       => ['java', 'kotlin'],
                'sql'        => ['sql'],
            ];
            $aliases = $langAliases[$techLang] ?? [$techLang];
            if (!empty(array_intersect($knownLanguages, $aliases))) {
                if ($objective === 'learning') {
                    $reasons[] = $isEn
                        ? '✓ Uses ' . ucfirst($tech['language'] ?? '') . ' which you already know — ideal progression'
                        : '✓ Utilise ' . ucfirst($tech['language'] ?? '') . ' que tu maîtrises déjà — progression idéale';
                } else {
                    $reasons[] = $isEn
                        ? ucfirst($tech['language'] ?? '') . ' language you already know'
                        : 'Langage ' . ucfirst($tech['language'] ?? '') . ' que tu connais déjà';
                }
            }
        }

        if (!empty($matchingUseCases)) {
            $reasons[] = ($isEn ? 'Great for: ' : 'Excellent pour : ') . implode(', ', $matchingUseCases);
        }

        if (!empty($matchingIntegrations)) {
            $reasons[] = ($isEn ? 'Compatible with: ' : 'Compatible avec : ') . implode(', ', $matchingIntegrations);
        }

        if (($tech['ecosystem'] ?? 0) >= 5) {
            $reasons[] = $isEn ? 'Very rich ecosystem' : 'Écosystème très riche';
        } elseif (($tech['ecosystem'] ?? 0) >= 4) {
            $reasons[] = $isEn ? 'Good ecosystem' : 'Bon écosystème';
        }

        if (($tech['performance'] ?? 0) >= 5) {
            $reasons[] = $isEn ? 'Excellent performance' : 'Excellentes performances';
        }

        if (($tech['community'] ?? 0) >= 5) {
            $reasons[] = $isEn ? 'Massive community' : 'Communauté massive';
        }

        if (($tech['maturity'] ?? 0) >= 5) {
            $reasons[] = $isEn ? 'Very mature and stable technology' : 'Technologie très mature et stable';
        }

        if (!$reasons) {
            $reasons[] = $tech['description'];
        }

        return implode('. ', $reasons) . '.';
    }

    private function getComplementaryLibraries(array $topRecommendations, array $features, string $lang = 'fr'): array
    {
        $libraries = [];
        $recommendedIds = array_map(fn($tech) => $tech['id'], $topRecommendations);
        $isEn = $lang === 'en';

        // Get integrations from recommended technologies
        foreach ($topRecommendations as $tech) {
            $integrations = $tech['integrations'] ?? [];
            foreach ($integrations as $integration) {
                $lib = $this->findTechnologyById($integration);
                if ($lib && !in_array($lib['id'], $recommendedIds) && !isset($libraries[$lib['id']])) {
                    $purpose = ($isEn && !empty($lib['description_en'])) ? $lib['description_en'] : ($lib['description'] ?? '');
                    $libraries[$lib['id']] = [
                        'name' => $lib['name'],
                        'purpose' => $purpose,
                        'reason' => $isEn
                            ? 'Recommended integration with ' . $tech['name']
                            : 'Intégration recommandée avec ' . $tech['name'],
                        'doc_link' => $lib['doc_link'] ?? '',
                    ];
                }
            }
        }

        // Add featured technologies if requested
        foreach ($features as $feature) {
            $tech = $this->findTechnologyById($feature);
            if ($tech && !in_array($tech['id'], $recommendedIds) && !isset($libraries[$tech['id']])) {
                $purpose = ($isEn && !empty($tech['description_en'])) ? $tech['description_en'] : ($tech['description'] ?? '');
                $libraries[$tech['id']] = [
                    'name' => $tech['name'],
                    'purpose' => $purpose,
                    'reason' => $isEn ? 'Requested technology' : 'Technology sollicitée',
                    'doc_link' => $tech['doc_link'] ?? '',
                ];
            }
        }

        return array_values($libraries);
    }

    private function generateSummary(
        array $topRecommendations,
        string $objective,
        string $profile,
        string $lang = 'fr'
    ): string {
        if (empty($topRecommendations)) {
            return $lang === 'en'
                ? 'No stack matches your criteria.'
                : 'Aucune stack ne correspond à vos critères.';
        }

        $primaryTech = $topRecommendations[0];
        $isEn = $lang === 'en';

        $objectiveText = $isEn
            ? match ($objective) {
                'learning'    => 'for fast learning',
                'mvp'         => 'for a quick MVP',
                'performance' => 'for best performance',
                default       => 'for a balanced project',
            }
            : match ($objective) {
                'learning'    => 'pour apprendre rapidement',
                'mvp'         => 'pour un MVP rapide',
                'performance' => 'pour les meilleures performances',
                default       => 'pour un projet équilibré',
            };

        $profileText = $isEn
            ? match ($profile) {
                'beginner' => 'suited for beginners',
                'advanced' => 'leveraging advanced features',
                default    => 'suited to your level',
            }
            : match ($profile) {
                'beginner' => 'adapté aux débutants',
                'advanced' => 'exploitant les fonctionnalités avancées',
                default    => 'adapté à votre niveau',
            };

        $techNames = array_map(fn($t) => $t['name'], array_slice($topRecommendations, 0, 2));

        return $isEn
            ? sprintf(
                '%s (%s) is recommended %s and %s. This stack benefits from a rich ecosystem and excellent documentation.',
                $primaryTech['name'],
                implode(', ', $techNames),
                $objectiveText,
                $profileText
            )
            : sprintf(
                '%s (%s) est recommandé %s et %s. Cette stack bénéficie d\'un écosystème riche et d\'une excellente documentation.',
                $primaryTech['name'],
                implode(', ', $techNames),
                $objectiveText,
                $profileText
            );
    }

    private function findTechnologyById(string $id): ?array
    {
        foreach ($this->technologies as $tech) {
            if ($tech['id'] === $id) {
                return $tech;
            }
        }
        return null;
    }

    public function getTechnologies(): array
    {
        return $this->technologies;
    }

    public function getTechnologyById(string $id): ?array
    {
        return $this->findTechnologyById($id);
    }

    public function compareTechnologies(array $ids): array
    {
        $technologies = [];
        foreach ($ids as $id) {
            $tech = $this->findTechnologyById($id);
            if ($tech) {
                $technologies[] = $tech;
            }
        }

        if (empty($technologies)) {
            return ['technologies' => [], 'comparison' => []];
        }

        // Build comparison matrix
        $comparison = [
            'learning_curve' => [],
            'performance' => [],
            'ecosystem' => [],
        ];

        foreach ($technologies as $tech) {
            $comparison['learning_curve'][$tech['name']] = $tech['learning_curve'] ?? 0;
            $comparison['performance'][$tech['name']] = $tech['performance'] ?? 0;
            $comparison['ecosystem'][$tech['name']] = $tech['ecosystem'] ?? 0;
        }

        return [
            'technologies' => $technologies,
            'comparison' => $comparison,
        ];
    }
}
