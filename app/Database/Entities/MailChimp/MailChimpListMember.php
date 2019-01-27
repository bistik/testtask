<?php
declare(strict_types=1);

namespace App\Database\Entities\MailChimp;

use Doctrine\ORM\Mapping as ORM;
use EoneoPay\Utils\Str;

/**
 * @ORM\Entity()
 */
class MailChimpListMember extends MailChimpEntity
{
    /**
     * @ORM\Column(name="email_address", type="string")
     *
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * @ORM\Column(name="mail_chimp_id", type="string", nullable=true)
     *
     * @var string
     */
    private $mailChimpId;

    /**
     * @ORM\Column(name="mail_chimp_list_id", type="string", nullable=true)
     *
     * @var string
     */
    private $mailChimpListId;

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $memberId;

    /**
     * @ORM\Column(name="tags", type="array", nullable=true)
     *
     * @var array
     */
    private $tags;

    /**
     * @ORM\Column(name="merge_fields", type="array", nullable=true)
     *
     * @var array
     */
    private $mergeFields;

    /**
     * @ORM\Column(name="interests", type="array", nullable=true)
     *
     * @var array
     */
    private $interests;

    /**
     * @ORM\Column(name="language", type="string", nullable=true)
     *
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(name="vip", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $vip;

    /**
     * @ORM\Column(name="location", type="array", nullable=true)
     *
     * @var array
     */
    private $location;

    /**
     * @ORM\Column(name="marketing_permissions", type="array", nullable=true)
     *
     * @var array
     */
    private $marketingPermissions;

    /**
     * @ORM\Column(name="ip_signup", type="string", nullable=true)
     *
     * @var string
     */
    private $ipSignup;

    /**
     * @ORM\Column(name="timestamp_signup", type="string", nullable=true)
     *
     * @var string
     */
    private $timestampSignup;

    /**
     * @ORM\Column(name="ip_opt", type="string", nullable=true)
     *
     * @var string
     */
    private $ipOpt;

    /**
     * @ORM\Column(name="timestamp_opt", type="string", nullable=true)
     *
     * @var string
     */
    private $timestampOpt;

    public function setEmailAddress (string $emailAddress) : MailChimpListMember
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function setStatus (string  $status) : MailChimpListMember
    {
        $this->status = $status;

        return $this;
    }

    public function setMailChimpId (string $mailchimpId) : MailChimpListMember
    {
        $this->mailChimpId = $mailchimpId;

        return $this;
    }

    public function setMailChimpListId (string $mailchimpListId) : MailChimpListMember
    {
        $this->mailChimpListId = $mailchimpListId;

        return $this;
    }

    public function setTags (array $tags) : MailChimpListMember
    {
        $this->tags = $tags;

        return $this;
    }

    public function setMergeFields (array $mergeFields) : MailChimpListMember
    {
        $this->mergeFields = $mergeFields;

        return $this;
    }

    public function setInterests (array $interests) : MailChimpListMember
    {
        $this->interests = $interests;

        return $this;
    }

    public function setLanguage (string $language) : MailChimpListMember
    {
        $this->language = $language;

        return $this;
    }

    public function setVip (bool $vip) : MailChimpListMember
    {
        $this->vip = $vip;

        return $this;
    }

    public function setLocation (array $location) : MailChimpListMember
    {
        $this->location = $location;

        return $this;
    }

    public function setMarketingPermissions (array $marketingPermissions) : MailChimpListMember
    {
        $this->marketingPermissions = $marketingPermissions;

        return $this;
    }

    public function setIpSignup (string $ipSignup) : MailChimpListMember
    {
        $this->ipSignup = $ipSignup;

        return $this;
    }

    public function setTimestampSignup (string $timestampSignup) : MailChimpListMember
    {
        $this->timestampSignup = $timestampSignup;

        return $this;
    }

    public function setIpOpt (string $ipOpt) : MailChimpListMember
    {
        $this->ipOpt = $ipOpt;

        return $this;
    }

    public function setTimestampOpt (string $timestampOpt) : MailChimpListMember
    {
        $this->timestampOpt = $timestampOpt;

        return $this;
    }

    public function getId(): string
    {
        return $this->memberId;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getMailChimpListId(): ?string
    {
        return $this->mailChimpListId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getMergeFields(): array
    {
        return $this->mergeFields;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getVip(): bool
    {
        return $this->vip;
    }

    public function getLocation(): array
    {
        return $this->location;
    }

    public function getMarketingPermissions(): array
    {
        return $this->marketingPermissions;
    }

    public function getIpSignup(): string
    {
        return $this->ipSignup;
    }

    public function getTimestampSignup(): string
    {
        return $this->timestampSignup;
    }

    public function getIpOpt(): string
    {
        return $this->ipOpt;
    }

    public function getTimestampOpt(): string
    {
        return $this->timestampOpt;
    }

    /**
     * Get validation rules for mailchimp entity.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'email_address' => 'required|email',
            'email_type' => 'nullable|in:html,text',
            'status' => 'required|string',
            'merge_fields' => 'nullable|array',
            'interests' => 'nullable|array',
            'interests.*' => 'nullable|string',
            'language' => 'nullable|string',
            'vip' => 'nullable|boolean',
            'location' => 'nullable|array',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'marketing_permissions' => 'nullable|array',
            'marketing_permissions.marketing_permission_id' => 'nullable|string',
            'marketing_permissions.enabled' => 'nullable|boolean',
            'ip_signup' => 'nullable|ip',
            'timestamp_signup' => 'nullable|date',
            'ip_opt' => 'nullable|ip',
            'timestamp_opt' => 'nullable|date',
        ];
    }

    /**
     * Get array representation of entity.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $str = new Str();

        foreach (\get_object_vars($this) as $property => $value) {
            $array[$str->snake($property)] = $value;
        }

        return $array;
    }
}