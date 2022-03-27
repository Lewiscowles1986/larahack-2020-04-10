<?php

namespace App\Seeds;

use Illuminate\Database\Seeder;

abstract class ContextualDatabaseSeeder extends Seeder
{
    public const ANY = '*';
    public const REVIEW_ONLY = 'REVIEW';
    public const PRODUCTION_ONLY = 'PRODUCTION';
    public const LOCAL_DEV_ONLY = 'LOCAL';
    public const REVIEW_AND_LOCAL = 'REVIEW+LOCAL';
    public const REVIEW_AND_PRODUCTION = 'REVIEW+PRODUCTION';
    public const PRODUCTION_AND_LOCAL = 'PRODUCTION+LOCAL';

    protected $_branch_name = null;
    protected $_guarded_environment = self::ANY;

    /**
     * Get Branch name
     */
    protected function deployed_branch() : ?string
    {
        return env("GIT_BRANCH", env("HEROKU_GIT_BRANCH"));
    }

    /**
     * Get Deployment type
     */
    protected function deployment_type() : ?string
    {
        return env("DEPLOYMENT_TYPE");
    }

    /**
    * Get Application Environment
    */
    protected function application_environment() : ?string
    {
        return env('APP_ENV');
    }

    /**
     * Only return true if:
     * 1. not a review app
     * 2. production or local guarded environment
     */
    protected function production_or_local() : bool
    {
        return (
            is_null($this->deployment_type()) &&
            in_array(
                $this->_guarded_environment,
                [
                    self::LOCAL_DEV_ONLY,
                    self::PRODUCTION_ONLY,
                    self::PRODUCTION_AND_LOCAL,
                    self::REVIEW_AND_LOCAL,
                    self::REVIEW_AND_PRODUCTION,
                ],
                true
            )
        );
    }

    /**
     * Only return true if a review app in review-compatible guarded environment
     */
    protected function review_app_only() : bool
    {
        return (
            $this->deployment_type() === "review" &&
            in_array(
                $this->_guarded_environment,
                [
                    self::REVIEW_ONLY,
                    self::REVIEW_AND_LOCAL,
                    self::REVIEW_AND_PRODUCTION,
                ],
                true
            )
        );
    }

    /**
     * Only return true if in production and not guarded to local dev only
     */
    protected function needs_production() : bool
    {
        return (
            $this->application_environment() === 'production' &&
            !in_array($this->_guarded_environment, [self::LOCAL_DEV_ONLY, self::REVIEW_AND_LOCAL], true)
        );
    }

    /**
     * Only return true if not in production and not guarded to production only
     */
    protected function local_compatible() : bool
    {
        return (
            $this->application_environment() !== 'production' &&
            !in_array($this->_guarded_environment, [self::PRODUCTION_ONLY, self::REVIEW_AND_PRODUCTION], true)
        );
    }

    /**
     * Can Be Run in given environment
     */
    protected function can_run_in_env() : bool
    {
        return (
            $this->_guarded_environment === self::ANY ||
            $this->review_app_only() || (
                $this->production_or_local() && (
                    $this->needs_production() ||
                    $this->local_compatible()
                )
            )
        );
    }

    /**
     * Predicate checking utility
     *
     * This checks a set of rules to see if the seeder should run
     */
    public function guard() : bool
    {
        return (
            ($this->_branch_name == $this->deployed_branch() || is_null($this->_branch_name)) &&
            $this->can_run_in_env()
        );
    }
}
