<?php
declare(strict_types=1);

namespace Tests\App\TestCases;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Console\Kernel;

abstract class WithDatabaseTestCase extends TestCase
{
    protected const MAILCHIMP_EXCEPTION_MESSAGE = 'MailChimp exception';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Create database using doctrine command.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(Kernel::class)->call('doctrine:schema:create');
        $this->entityManager = $this->app->make(EntityManagerInterface::class);
    }

    /**
     * Drop database using doctrine command.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->app->make(Kernel::class)->call('doctrine:schema:drop', ['--force' => true]);

        parent::tearDown();
    }
}
