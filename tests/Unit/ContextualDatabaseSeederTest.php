<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Seeds\ContextualDatabaseSeeder;

class ContextualTestSeeder extends ContextualDatabaseSeeder {
    public const VALID_ENVIRONMENTS = [
        ContextualDatabaseSeeder::ANY,
        ContextualDatabaseSeeder::REVIEW_ONLY,
        ContextualDatabaseSeeder::PRODUCTION_ONLY,
        ContextualDatabaseSeeder::LOCAL_DEV_ONLY,
        ContextualDatabaseSeeder::REVIEW_AND_LOCAL,
        ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION,
        ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL,
    ];

    /**
     * Specific implementation to aid testing without farting about with environment
     */
    public function __construct(?string $applicationEnv, ?string $deployType, ?string $deployedBranch, ?string $branchName, string $guardedEnvironment=ContextualDatabaseSeeder::ANY)
    {
        $this->_branch_name = $branchName;
        if (!in_array($guardedEnvironment, self::VALID_ENVIRONMENTS)) {
            trigger_error("Invalid guarded environment", E_USER_ERROR);
        }
        $this->_guarded_environment = $guardedEnvironment;

        $this->_deployed_branch = $deployedBranch;
        $this->_deployment_type = $deployType;
        $this->_application_environment = $applicationEnv;
    }

    protected function deployed_branch() : ?string { return $this->_deployed_branch; }
    protected function deployment_type() : ?string { return $this->_deployment_type; }
    protected function application_environment() : ?string { return $this->_application_environment; }
}

class ContextualDatabaseSeederTest extends TestCase
{
    /**
     * Comprehensively test the guard clause
     *
     * @dataProvider guardClauseProvider
     *
     * @return void
     */
    public function testGuardClause(array $args, bool $result)
    {
        $seeder = new ContextualTestSeeder(...$args);
        $this->assertEquals($seeder->guard(), $result);
    }

    public function guardClauseProvider() {
        return [
            // In all these cases just use a regular seeder
            'Local dev should pass empty guard?' => [['local', null, null, null], true],
            'Production should pass empty guard?' => [['production', null, null, null], true],
            'Review App should pass empty guard?' => [['production', 'review', null, null], true],
            'Local dev App, no branch guard with branch, any ENV' => [['local', null, 'anything', null], true],
            'Review App, no branch guard with branch, any ENV' => [['production', 'review', 'anything', null], true],
            'Production App, no branch guard with branch, any ENV' => [['production', null, 'anything', null], true],

            // Guarding when local deployed (ignore branch matching cases)
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['local', null, null, null, ContextualDatabaseSeeder::LOCAL_DEV_ONLY], true],
            [['local', null, null, null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['local', null, null, null, ContextualDatabaseSeeder::PRODUCTION_ONLY], false],
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],

            // Guarding when local deployed (branch matching cases)
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['local', null, null, 'wontmatch', ContextualDatabaseSeeder::REVIEW_AND_LOCAL], false],
            [['local', null, null, null, ContextualDatabaseSeeder::LOCAL_DEV_ONLY], true],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::LOCAL_DEV_ONLY], true],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::LOCAL_DEV_ONLY], true],
            [['local', null, null, 'wontmatch', ContextualDatabaseSeeder::LOCAL_DEV_ONLY], false],
            [['local', null, null, null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['local', null, null, 'wontmatch', ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], false],

            // Guarding when local deployed (branch matching cases)
            [['local', null, null, null, ContextualDatabaseSeeder::PRODUCTION_ONLY], false],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::PRODUCTION_ONLY], false],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::PRODUCTION_ONLY], false],

            // Guarding when local deployed with guard for non-local
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['local', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],
            [['local', null, 'anything', null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],
            [['local', null, 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],

            // Guarding when production deployed (ignore branch matching cases)
            [['production', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], false],
            [['production', null, null, null, ContextualDatabaseSeeder::LOCAL_DEV_ONLY], false],
            [['production', null, null, null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['production', null, null, null, ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['production', null, null, null, ContextualDatabaseSeeder::PRODUCTION_ONLY], true],
            [['production', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],

            // Guarding when production deployed (branch matching cases)
            [['production', null, null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', null, 'anything', null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', null, 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', null, null, 'wontmatch', ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],
            [['production', null, null, null, ContextualDatabaseSeeder::PRODUCTION_ONLY], true],
            [['production', null, 'anything', null, ContextualDatabaseSeeder::PRODUCTION_ONLY], true],
            [['production', null, 'anything', 'anything', ContextualDatabaseSeeder::PRODUCTION_ONLY], true],
            [['production', null, null, 'wontmatch', ContextualDatabaseSeeder::PRODUCTION_ONLY], false],
            [['production', null, null, null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['production', null, 'anything', null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['production', null, 'anything', 'anything', ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], true],
            [['production', null, null, 'wontmatch', ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], false],

            // Guarding when review deployed (ignore branch matching cases)
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['production', 'review', null, null, ContextualDatabaseSeeder::LOCAL_DEV_ONLY], false],
            [['production', 'review', null, null, ContextualDatabaseSeeder::PRODUCTION_AND_LOCAL], false],
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_ONLY], true],
            [['production', 'review', null, null, ContextualDatabaseSeeder::PRODUCTION_ONLY], false],
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],

            // Guarding when production deployed (branch matching cases)
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', 'review', 'anything', null, ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', 'review', 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], true],
            [['production', 'review', null, 'wontmatch', ContextualDatabaseSeeder::REVIEW_AND_PRODUCTION], false],
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_ONLY], true],
            [['production', 'review', 'anything', null, ContextualDatabaseSeeder::REVIEW_ONLY], true],
            [['production', 'review', 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_ONLY], true],
            [['production', 'review', null, 'wontmatch', ContextualDatabaseSeeder::REVIEW_ONLY], false],
            [['production', 'review', null, null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['production', 'review', 'anything', null, ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['production', 'review', 'anything', 'anything', ContextualDatabaseSeeder::REVIEW_AND_LOCAL], true],
            [['production', 'review', null, 'wontmatch', ContextualDatabaseSeeder::REVIEW_AND_LOCAL], false],

            'Review App, branch guard without branch, any ENV?' => [['production', 'review', null, 'wontmatch'], false],
        ];
    }
}
