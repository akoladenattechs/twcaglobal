<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Smoke test: the admin login page must be reachable.
     * (The previous scaffold test hit the public homepage, which requires
     * installer-seeded tables like hero_settings that don't exist in the
     * migration set — so it could never pass in this project.)
     */
    public function test_admin_login_page_is_reachable(): void
    {
        $this->get(route('admin.login'))->assertOk();
    }
}
