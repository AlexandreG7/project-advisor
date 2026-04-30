<?php

namespace App\DataFixtures;

use App\Entity\Recommendation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SampleRecommendations extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Sample recommendation 1: MVP Fast Web App
        $rec1 = new Recommendation();
        $rec1->setAnswersJson(json_encode([
            'objective' => 'mvp',
            'profile' => 'intermediate',
            'useCases' => ['web-app', 'e-commerce'],
            'features' => ['typescript', 'tailwind'],
            'constraints' => [],
            'preferences' => [],
        ]));
        $rec1->setResultJson(json_encode([
            'stack' => [
                [
                    'id' => 'nextjs',
                    'name' => 'Next.js',
                    'category' => 'framework',
                    'score' => 4.5,
                    'justification' => 'Excellent pour le MVP rapide',
                ],
            ],
            'libraries' => [],
            'summary' => 'Next.js est recommandé pour un MVP rapide',
        ]));
        $manager->persist($rec1);

        // Sample recommendation 2: Learning Path
        $rec2 = new Recommendation();
        $rec2->setAnswersJson(json_encode([
            'objective' => 'learning',
            'profile' => 'beginner',
            'useCases' => ['web-app'],
            'features' => [],
            'constraints' => [],
            'preferences' => [],
        ]));
        $rec2->setResultJson(json_encode([
            'stack' => [],
            'libraries' => [],
            'summary' => 'Recommandation pour apprentissage',
        ]));
        $manager->persist($rec2);

        $manager->flush();
    }
}
