<?php
declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use App\Database\Entities\MailChimp\MailChimpListMember;
use Illuminate\Http\JsonResponse;
use Mailchimp\Mailchimp;
use Mockery;
use Mockery\MockInterface;
use Tests\App\TestCases\WithDatabaseTestCase;

abstract class MembersTestCase extends WithDatabaseTestCase {

    /**
     * @var array
     */

    protected static $memberData = [
        'email_address' => 'erick+test@loyaltycorp.com',
        'status' => 'subscribed',
        'tags' => ['tag1', 'test-tag'],
        'merge_fields' => [
            'FNAME' => 'Erick',
            'LNAME' => 'Test',
        ],
        'language' => 'en',
        'ip_signup' => '1.2.3.4',
        'ip_opt' => '1.2.3.4',
        'timestamp_signup' => '2019-01-26 15:59:30',
        'timestamp_opt' => '2019-01-26 15:59:59',
        'vip' => true,
        'location' => [
            'latitude' => '14.560915',
            'longitude' => '121.026314',
        ]
    ];

    protected static $listId;

    public function setUp(): void
    {
        parent::setUp();

        $mailChimp = $this->app->make(Mailchimp::class);

        $response = $mailChimp->post('lists', [
            'name' => 'Coding test',
            'permission_reminder' => 'Coding exam.',
            'email_type_option' => false,
            'contact' => [
                'company' => 'Doe Ltd.',
                'address1' => 'DoeStreet 1',
                'address2' => '',
                'city' => 'Doesy',
                'state' => 'Doedoe',
                'zip' => '1672-12',
                'country' => 'US',
                'phone' => '55533344412'
            ],
            'campaign_defaults' => [
                'from_name' => 'John Doe',
                'from_email' => 'john@doe.com',
                'subject' => 'My new campaign!',
                'language' => 'US'
            ],
            'visibility' => 'prv',
            'use_archive_bar' => false,
            'notify_on_subscribe' => 'notify@loyaltycorp.com.au',
            'notify_on_unsubscribe' => 'notify@loyaltycorp.com.au'
        ]);

        self::$listId = $response->get('id');
    }

    /**
     * Call MailChimp to delete members created during test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        if (self::$listId) {
            $mailChimp = $this->app->make(Mailchimp::class);
            $mailChimp->delete(\sprintf('lists/%s', self::$listId));
        }

        parent::tearDown();
    }

    protected function createMember(array $data): MailChimpListMember
    {
        $member = new MailChimpListMember($data);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return $member;
    }

    protected function assertMailChimpExceptionResponse(JsonResponse $response): void
    {
        $content = \json_decode($response->content(), true);

        self::assertEquals(400, $response->getStatusCode());
        self::assertArrayHasKey('message', $content);
        self::assertEquals(self::MAILCHIMP_EXCEPTION_MESSAGE, $content['message']);
    }

    /**
     * Returns mock of MailChimp to throw exception when requesting their API.
     *
     * @param string $method
     *
     * @return \Mockery\MockInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Mockery requires static access to mock()
     */
    protected function mockMailChimpForException(string $method): MockInterface
    {
        $mailChimp = Mockery::mock(MailChimp::class);

        $mailChimp
            ->shouldReceive($method)
            ->once()
            ->withArgs(function (string $method, ?array $options = null) {
                return !empty($method) && (null === $options || \is_array($options));
            })
            ->andThrow(new \Exception('MailChimp exception'));

        return $mailChimp;
    }
}