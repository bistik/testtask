<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\MembersTestCase;

/**
 * @group members
 */
class MembersControllerTest extends MembersTestCase {

    /**
     * Test application creates list member and returns it back with id from MailChimp.
     *
     * @return void
     * @group members-create
     */
    public function testCreateMemberSuccessfully(): void
    {
        static::$memberData['email_address'] = sprintf('foobar+test%d@gmail.com', time());
        $this->post('/mailchimp/lists/' . static::$listId . '/members', static::$memberData);

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$memberData);
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertArrayHasKey('member_id', $content);
        self::assertArrayHasKey('email_address', $content);
        self::assertArrayHasKey('status', $content);
        self::assertArrayHasKey('tags', $content);
        self::assertArrayHasKey('merge_fields', $content);
        self::assertNotNull($content['member_id']);
    }

    /**
     * Test validation
     *
     * @return void
     * @group members-validation
     */
    public function testRequiredCreateMember(): void
    {
        $this->post('/mailchimp/lists/' . static::$listId . '/members');

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);

        // required fields
        self::assertArrayHasKey('email_address', $content['errors']);
        self::assertArrayHasKey('status', $content['errors']);
    }

    /**
     * Test validation
     *
     * @group members-validation
     */
    public function testInvalidCreateMember(): void
    {
        $this->post('/mailchimp/lists/' . static::$listId . '/members', [
            'email_address' => 'not-an-email-address',
            'status' => 'test',
            'ip_signup' => 'not-an-ip',
            'ip_opt' => 'not-an-ip',
            'timestamp_signup' => 'not-datetime',
            'timestamp_opt' => 'not-datetime',
            'location' => [
                'latitude' => 'not-a-number',
                'longitude' => 'not-a-number'
            ],
        ]);
        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);

        // invalid data type
        self::assertArrayHasKey('email_address', $content['errors']);
        self::assertArrayHasKey('ip_signup', $content['errors']);
        self::assertArrayHasKey('ip_opt', $content['errors']);
        self::assertArrayHasKey('location.latitude', $content['errors']);
        self::assertArrayHasKey('location.longitude', $content['errors']);
        self::assertArrayHasKey('timestamp_signup', $content['errors']);
        self::assertArrayHasKey('timestamp_opt', $content['errors']);
    }

    /**
     * Test show members
     *
     * @group members-show
     */
    public function testShowMembers(): void
    {
        static::$memberData['mail_chimp_list_id'] = static::$listId;
        $this->createMember(static::$memberData);

        $this->get('/mailchimp/lists/' . static::$listId . '/members');

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        self::assertGreaterThanOrEqual(count($content), 1);
        foreach (static::$memberData as $key => $value) {
            self::assertArrayHasKey($key, $content[0]);
            self::assertEquals($value, $content[0][$key]);
        }
    }

    /**
     * Test non-existent list or empty list
     *
     * @group members-show-empty
     */
    public function testEmptyMembers(): void
    {
        $this->get('/mailchimp/lists/' . static::$listId . '/members');

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals('Members for MailChimp list [' . static::$listId . '] not found', $content['message']);

        $this->get('/mailchimp/lists/not-an-existing-list/members');

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals('Members for MailChimp list [not-an-existing-list] not found', $content['message']);
    }

    /**
     * Test delete a member
     *
     * @group members-delete
     */
    public function testDeleteMember(): void
    {
        static::$memberData['email_address'] = time() . '+test@loyalycorp.com';
        static::$memberData['mail_chimp_list_id'] = static::$listId;
        $this->post('/mailchimp/lists/' . static::$listId . '/members', static::$memberData);

        $content = json_decode($this->response->getContent(), true);

        $uri = '/mailchimp/lists/' . static::$listId . '/members/' . $content['member_id'];
        $this->delete($uri);

        $this->assertResponseOk();
        self::assertEmpty(json_decode($this->response->content(), true));
    }

    /**
     * Test update a member
     *
     * @group members-update
     */
    public function testUpdateMember(): void
    {
        static::$memberData['email_address'] = time() . '+test@loyalycorp.com';
        static::$memberData['mail_chimp_list_id'] = static::$listId;
        $this->post('/mailchimp/lists/' . static::$listId . '/members', static::$memberData);
        $content = json_decode($this->response->content(), true);
        $memberId = $content['member_id'];


        $this->put('/mailchimp/lists/' . static::$listId . '/members/' . $memberId, [
            'merge_fields' => [
                'FNAME' => 'Updated fname',
                'LNAME' => 'Updated lname',
            ],
            'status' => 'unsubscribed'
        ]);

        $content = json_decode($this->response->content(), true);
        $this->assertResponseOk();

        foreach (array_keys(static::$memberData) as $key) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals('Updated fname', $content['merge_fields']['FNAME']);
            self::assertEquals('Updated lname', $content['merge_fields']['LNAME']);
            self::assertEquals('unsubscribed', $content['status']);
        }
    }

    /**
     * Test create member on invalid list
     *
     * @group members-create
     * @group members-create-no-list
     */
    public function testInvalidListCreateMember(): void
    {
        $this->post('/mailchimp/lists/list-does-not-exist/members', [
            'email_address' => 'erick+test-not-list@yahoo.com',
            'status' => 'subscribed',
        ]);
        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals('MailChimpList[list-does-not-exist] not found', $content['message']);
    }
}