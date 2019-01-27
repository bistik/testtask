<?php
declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use App\Database\Entities\MailChimp\MailChimpListMember;
use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mailchimp\Mailchimp;

class MembersController extends Controller
{

    private $mailChimp;

    public function __construct(EntityManagerInterface $entityManager, MailChimp $mailchimp)
    {
        parent::__construct($entityManager);

        $this->mailChimp = $mailchimp;
    }

    public function create (string $listId, Request $request): JsonResponse
    {
        // Instantiate entity
        $member = new MailChimpListMember($request->all());

        // Validate entity
        $validator = $this->getValidationFactory()->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            // Return error response if validation failed
            return $this->errorResponse([
                'message' => 'Invalid data given',
                'errors' => $validator->errors()->toArray()
            ]);
        }


        try {
            $response = $this->mailChimp->post('lists/' . $listId . '/members', $member->toMailChimpArray());
            $member->setMailChimpListId($response->get('list_id'));
            $member->setMailChimpId($response->get('id'));

            $this->saveEntity($member);
        } catch (Exception $exception) {
            // Return error response if something goes wrong
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }

    public function show (string $listId): JsonResponse
    {
        $members = $this->entityManager->getRepository(MailChimpListMember::class)
                            ->findBy(['mailChimpListId' => $listId]);

        if (empty($members)) {
            return $this->errorResponse(
                ['message' => \sprintf('Members for MailChimp list [%s] not found', $listId)],
                404
            );
        }
        $arList = [];
        foreach ($members as $member) {
            $arList[] = $member->toArray();
        }
        return $this->successfulResponse($arList);
    }

    public function remove (string $listId, string $memberId): JsonResponse
    {
        $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpListMember[%s] not found', $memberId)],
                404
            );
        }

        $member->setMailChimpListId($listId);

        try {
            // Remove list from MailChimp
            $subHash = md5(strtolower($member->getEmailAddress()));
            $mcListId = $member->getMailChimpListId();
            $this->mailChimp->delete(sprintf('lists/%s/members/%s', $mcListId, $subHash));
            // Remove member from database
            $this->removeEntity($member);
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse([]);
    }

    public function update (string $listId, string $memberId, Request $request): JsonResponse
    {
        $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpListMember[%s] not found', $memberId)],
                404
            );
        }

        $member->fill($request->all());

        // Validate entity
        $validator = $this->getValidationFactory()->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            // Return error response if validation failed
            return $this->errorResponse([
                'message' => 'Invalid data given',
                'errors' => $validator->errors()->toArray()
            ]);
        }

        try {
            $this->saveEntity($member);
            $subHash = md5(strtolower($member->getEmailAddress()));
            $this->mailChimp->patch(\sprintf('lists/%s/members/%s', $listId, $subHash), $member->toMailChimpArray());
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }
}