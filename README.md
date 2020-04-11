# LaraHack Community project

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

## Contributing

Thank you for considering contributing to this application. Raise an issue, submit a PR. Be cool. It's likely we'll create a review preview instance for your contributions using Heroku Review apps.

Please include separate PR-testing seeds as files with environment guards that check for the `DEPLOYMENT_TYPE` which will be the lowercase string "review" and have the `HEROKU_GIT_BRANCH` equal to your branch name.

<details>
<summary>Example of a contextual database seeder</summary>

```php
<?php

class MyDatabaseSeeder extends ContextualDatabaseSeeder
{
  /**
   * If this is null it will run on any branch.
   * Otherwise branches matching (currently exact match only)
   */
  protected $_branch_name = null;
  /**
   * Can be one of:
   * [
   *   ANY, REVIEW_ONLY, PRODUCTION_ONLY, LOCAL_DEV_ONLY,
   *   REVIEW_AND_LOCAL, REVIEW_AND_PRODUCTION, PRODUCTION_AND_LOCAL
   * ]
   */
  protected $_guarded_environment = ContextualDatabaseSeeder::ANY;

  /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if(!$this->guard()) { return; }
        // Whatever seeding needs doing
    }
}
```

</details>

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This work will follow that license.
