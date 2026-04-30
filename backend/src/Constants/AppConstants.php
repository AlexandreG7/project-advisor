<?php

namespace App\Constants;

final class AppConstants
{
    public const VERSION = '1.0.0';
    public const API_VERSION = 'v1';
    public const APP_NAME = 'ProjectAdvisor';

    // Recommendation objectives
    public const OBJECTIVE_LEARNING = 'learning';
    public const OBJECTIVE_MVP = 'mvp';
    public const OBJECTIVE_PERFORMANCE = 'performance';
    public const OBJECTIVE_BALANCED = 'balanced';

    public const OBJECTIVES = [
        self::OBJECTIVE_LEARNING,
        self::OBJECTIVE_MVP,
        self::OBJECTIVE_PERFORMANCE,
        self::OBJECTIVE_BALANCED,
    ];

    // User profiles
    public const PROFILE_BEGINNER = 'beginner';
    public const PROFILE_INTERMEDIATE = 'intermediate';
    public const PROFILE_ADVANCED = 'advanced';

    public const PROFILES = [
        self::PROFILE_BEGINNER,
        self::PROFILE_INTERMEDIATE,
        self::PROFILE_ADVANCED,
    ];

    // Advice request statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ANSWERED,
        self::STATUS_ARCHIVED,
    ];

    // Constraints
    public const CONSTRAINT_NO_DATABASE = 'no-database';
    public const CONSTRAINT_NO_BACKEND = 'no-backend';

    // Common use cases
    public const USE_CASE_WEB_APP = 'web-app';
    public const USE_CASE_E_COMMERCE = 'e-commerce';
    public const USE_CASE_BLOG = 'blog';
    public const USE_CASE_API = 'api';
    public const USE_CASE_SPA = 'spa';
    public const USE_CASE_PWA = 'pwa';
}
