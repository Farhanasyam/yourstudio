<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** @var string[] failed checks collected by check() */
    private array $failedChecks = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests rebuild the database. Never let that touch the real data.
        $database = DB::connection()->getDatabaseName();
        if (!str_ends_with((string) $database, '_test')) {
            $this->fail("Tests must run on a *_test database, not \"{$database}\". Check DB_DATABASE in phpunit.xml.");
        }
    }

    /**
     * Record one scenario check. Unlike an assertion it doesn't stop the test,
     * so one run reports every broken scenario; call assertChecksPassed() at the end.
     */
    protected function check(string $name, bool $ok, string $info = ''): void
    {
        if (!$ok) {
            $this->failedChecks[] = $name . ($info !== '' ? "  [{$info}]" : '');
        }
        $this->addToAssertionCount(1);
    }

    protected function assertChecksPassed(): void
    {
        $this->assertSame([], $this->failedChecks, "Failed checks:\n- " . implode("\n- ", $this->failedChecks));
    }

    /** Status plus redirect / exception / validation details, for check() messages. */
    protected function describe($response): string
    {
        $status = $response->getStatusCode();
        $extra = '';
        if ($status >= 500 && $response->exception) {
            $extra = ' ' . get_class($response->exception) . ': ' . mb_substr($response->exception->getMessage(), 0, 200);
        }
        if ($status >= 300 && $status < 400) {
            $extra = ' -> ' . $response->headers->get('Location');
        }
        if ($errors = session('errors')) {
            $extra .= ' errors=' . json_encode($errors->getBag('default')->toArray());
        }
        if (session('error')) {
            $extra .= ' error=' . session('error');
        }
        return $status . $extra;
    }
}
