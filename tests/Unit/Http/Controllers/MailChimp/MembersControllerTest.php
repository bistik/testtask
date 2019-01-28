<?php
declare(strict_types=1);

namespace Tests\App\Unit\Http\Controllers\MailChimp;

use App\Http\Controllers\MailChimp\MembersController;
use Tests\App\TestCases\MailChimp\MembersTestCase;
use App\Database\Entities\MailChimp\MailChimpListMember;

/**
 * @group members
 */
class MembersControllerTest extends MembersTestCase
{
    /**
     * Test controller returns error response when exception is thrown during create MailChimp request.
     *
     * @group members-create
     * @return void
     */
    public function testCreateMemberMailChimpException(): void
    {
        /** @noinspection PhpParamsInspection Mock given on purpose */
        $controller = new MembersController($this->entityManager, $this->mockMailChimpForException('post'));

        $this->assertMailChimpExceptionResponse($controller->create(static::$listId, $this->getRequest(static::$memberData)));
    }


    /**
     * @group members-delete
     */
    public function testRemoveMemberMailChimpException(): void
    {
        $controller = new MembersController($this->entityManager, $this->mockMailChimpForException('delete'));

        $member = $this->createMember(static::$memberData);

        if (null === $member->getId()) {
            self::markTestSkipped('Unable to remove, no member found');
            return;
        }

        $this->assertMailChimpExceptionResponse($controller->remove(static::$listId, $member->getId()));
    }

    /**
     * @group members-update
     */
    public function testUpdateMemberMailChimpException(): void
    {
        $controller = new MembersController($this->entityManager, $this->mockMailChimpForException('patch'));

        $member = $this->createMember(static::$memberData);

        // If there is no list id, skip
        if (null === $member->getId()) {
            self::markTestSkipped('Unable to remove, no member found');
            return;
        }

        $this->assertMailChimpExceptionResponse($controller->update(static::$listId, $member->getId(), $this->getRequest()));
    }

    /**
     * @group members-create
     */
    public function testCreateMemberSuccess(): void
    {
        $controller = new MembersController($this->entityManager, $this->mockMailChimpMember('post'));

        $request = $this->postRequest(static::$memberData);
        $this->assertMailChimpSuccessResponse($controller->create(static::$listId, $request));

        $members = $this->entityManager->getRepository(MailChimpListMember::class)
           ->findAll();
        $this->assertEquals(1, count($members));
        $this->assertEquals(static::$memberData['email_address'], $members[0]->getEmailAddress());
    }

    /**
     * @group members-delete
     */
    public function testRemoveMemberSuccess(): void
    {
        $controller = new MembersController($this->entityManager, $this->mockMailChimpMember('post'));

        $request = $this->postRequest(static::$memberData);
        $this->assertMailChimpSuccessResponse($controller->create(static::$listId, $request));
        $members = $this->entityManager->getRepository(MailChimpListMember::class)
            ->findAll();
        $member = $members[0];

        $controller = new MembersController($this->entityManager, $this->mockMailChimpMember('delete'));
        $this->assertMailChimpSuccessResponse($controller->remove(static::$listId, $member->getId()));
        $members = $this->entityManager->getRepository(MailChimpListMember::class)
            ->findAll();
        $this->assertEmpty($members);
    }

    /**
     * @group members-update
     */
    public function testUpdateSuccess(): void
    {
        $controller = new MembersController($this->entityManager, $this->mockMailChimpMember('post'));
        $request = $this->postRequest(static::$memberData);
        $this->assertMailChimpSuccessResponse($controller->create(static::$listId, $request));
        $members = $this->entityManager->getRepository(MailChimpListMember::class)
            ->findAll();
        $member = $members[0];

        $controller = new MembersController($this->entityManager, $this->mockMailChimpMember('patch'));
        $request = $this->postRequest(['status' => 'unsubscribed']);
        $this->assertMailChimpSuccessResponse($controller->update(static::$listId, $member->getId(), $request));
        $members = $this->entityManager->getRepository(MailChimpListMember::class)
            ->findAll();
        $member = $members[0];
        $this->assertEquals($member->getStatus(), 'unsubscribed');
    }
}