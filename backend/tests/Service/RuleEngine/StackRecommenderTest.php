<?php

namespace App\Tests\Service\RuleEngine;

use App\Service\RuleEngine\StackRecommender;
use PHPUnit\Framework\TestCase;

class StackRecommenderTest extends TestCase
{
    private StackRecommender $recommender;

    protected function setUp(): void
    {
        $projectDir = dirname(__DIR__, 3);
        $this->recommender = new StackRecommender($projectDir);
    }

    public function testLoadTechnologies(): void
    {
        $techs = $this->recommender->getTechnologies();
        $this->assertIsArray($techs);
        $this->assertGreaterThan(0, count($techs));
    }

    public function testGetTechnologyById(): void
    {
        $tech = $this->recommender->getTechnologyById('nextjs');
        $this->assertNotNull($tech);
        $this->assertEquals('nextjs', $tech['id']);
        $this->assertEquals('Next.js', $tech['name']);
    }

    public function testRecommendWithBalancedObjective(): void
    {
        $answers = [
            'objective' => 'balanced',
            'profile' => 'intermediate',
            'useCases' => ['web-app'],
            'features' => ['typescript'],
            'constraints' => [],
            'preferences' => [],
        ];

        $result = $this->recommender->recommend($answers);

        $this->assertArrayHasKey('stack', $result);
        $this->assertArrayHasKey('libraries', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('metadata', $result);

        $this->assertIsArray($result['stack']);
        $this->assertGreaterThan(0, count($result['stack']));

        $topTech = $result['stack'][0];
        $this->assertArrayHasKey('name', $topTech);
        $this->assertArrayHasKey('score', $topTech);
        $this->assertArrayHasKey('justification', $topTech);
    }

    public function testRecommendWithLearningObjective(): void
    {
        $answers = [
            'objective' => 'learning',
            'profile' => 'beginner',
            'useCases' => ['web-app'],
            'features' => [],
            'constraints' => [],
            'preferences' => [],
        ];

        $result = $this->recommender->recommend($answers);

        $this->assertArrayHasKey('stack', $result);
        $this->assertGreaterThan(0, count($result['stack']));

        // Verify metadata
        $this->assertEquals('learning', $result['metadata']['objective']);
        $this->assertEquals('beginner', $result['metadata']['profile']);
    }

    public function testRecommendWithMVPObjective(): void
    {
        $answers = [
            'objective' => 'mvp',
            'profile' => 'intermediate',
            'useCases' => ['web-app', 'e-commerce'],
            'features' => ['typescript', 'tailwind', 'stripe'],
            'constraints' => [],
            'preferences' => [],
        ];

        $result = $this->recommender->recommend($answers);

        $this->assertArrayHasKey('libraries', $result);
        $this->assertIsArray($result['libraries']);
    }

    public function testCompareTechnologies(): void
    {
        $ids = ['nextjs', 'nuxt', 'astro'];
        $comparison = $this->recommender->compareTechnologies($ids);

        $this->assertArrayHasKey('technologies', $comparison);
        $this->assertArrayHasKey('comparison', $comparison);
        $this->assertCount(3, $comparison['technologies']);

        $this->assertArrayHasKey('learning_curve', $comparison['comparison']);
        $this->assertArrayHasKey('performance', $comparison['comparison']);
        $this->assertArrayHasKey('ecosystem', $comparison['comparison']);
    }

    public function testRecommendFiltersIncompatible(): void
    {
        $answers = [
            'objective' => 'balanced',
            'profile' => 'intermediate',
            'useCases' => ['api'],
            'features' => [],
            'constraints' => ['no-backend'],
            'preferences' => [],
        ];

        $result = $this->recommender->recommend($answers);

        // Should not recommend backend technologies
        foreach ($result['stack'] as $tech) {
            $this->assertNotIn('backend', (array)($tech['type'] ?? ''));
        }
    }
}
