<?php

declare(strict_types = 1);

namespace Symfony\Component\Notifier\Bridge\Iterable;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

/**
 * @author Balázs Csaba <csaba.balazs@lingoda.com>
 *
 * @see https://api.iterable.com/api/docs#push_target
 */
class IterableOptions implements MessageOptionsInterface
{
    /**
     * @var array<mixed>
     *
     * @see https://api.iterable.com/api/docs#schema-push_target_body
     */
    protected $options;

    /**
     * @var int|null
     */
    private $campaignId;

    /**
     * @param array<mixed> $options
     */
    public function __construct(array $options = [], ?int $campaignId = null)
    {
        $this->options = $options;
        $this->campaignId = $campaignId;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->options, ['campaignId' => $this->campaignId]);
    }

    public function getRecipientId(): ?string
    {
        return $this->options['recipientEmail'] ?? $this->options['recipientUserId'] ?? null;
    }

    public function getCampaignId(): ?int
    {
        return $this->campaignId;
    }

    /**
     * Either email or userId must be passed in to identify the user. If both are passed in, email takes precedence
     */
    public function recipientEmail(string $recipientEmail): self
    {
        $this->options['recipientEmail'] = $recipientEmail;

        return $this;
    }

    /**
     * UserId that was passed into the updateUser call
     */
    public function recipientUserId(string $recipientUserId): self
    {
        $this->options['recipientUserId'] = $recipientUserId;

        return $this;
    }

    /**
     * JSON object containing fields to merge into email template
     *
     * @param array<mixed> $dataFields
     */
    public function dataFields(array $dataFields): self
    {
        $this->options['dataFields'] = $dataFields;

        return $this;
    }

    /**
     * Schedule the message for up to 365 days in the future. If set in the past, message is sent immediately.
     * Format is YYYY-MM-DD HH:MM:SS in UTC
     */
    public function sendAt(string $sendAt): self
    {
        $this->options['sendAt'] = $sendAt;

        return $this;
    }

    /**
     * Allow repeat marketing sends? Defaults to true
     */
    public function allowRepeatMarketingSends(bool $allowRepeatMarketingSends): self
    {
        $this->options['allowRepeatMarketingSends'] = $allowRepeatMarketingSends;

        return $this;
    }

    /**
     * Metadata to pass back via system webhooks. Not used for rendering
     *
     * @param array<mixed> $metadata
     */
    public function metadata(array $metadata): self
    {
        $this->options['metadata'] = $metadata;

        return $this;
    }
}
