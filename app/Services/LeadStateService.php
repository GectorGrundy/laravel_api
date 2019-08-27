<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Integration\IncorrectLeadStatusException;
use App\Models\Lead;

class LeadStateService
{
    public const INTEGRATED = 'integrated';
    public const APPROVE = 'approve';
    public const CANCEL = 'cancel';
    public const TRASH = 'trash';

    private $lead;
    private $state;
    private $bad_state = false;

//    public static function validateStateTransition(Lead $lead, string $state)
//    {
//        (new self)->validateAction($lead, $state);
//    }
//
//    private function validateAction(Lead $lead, string $state): void
//    {
//        $this->lead = $lead;
//        $this->state = $state;
//
//        $this->validateIntegratedState();
//        $this->validateApproveState();
//
//        if ($this->bad_state) {
//            throw new IncorrectLeadStatusException($lead['id'], $state);
//        }
//    }

    private static function throwAnException($lead, $state): void
    {
        throw new IncorrectLeadStatusException($lead['id'], $state);
    }

    public static function validateIntegratedState(Lead $lead): void
    {

        if ($lead['status'] !== Lead::NEW) {
            self::throwAnException($lead, self::INTEGRATED);
        }
    }

    public static function validateApproveState(Lead $lead): void
    {
        if ($lead['status'] === Lead::APPROVED) {
            self::throwAnException($lead, self::APPROVE);
        }
    }

    public static function validateCancelState(Lead $lead): void
    {
        if ($lead['status'] === Lead::CANCELLED) {
            self::throwAnException($lead, self::CANCEL);
        }
    }

    public static function validateTrashState(Lead $lead): void
    {
        if ($lead['status'] === Lead::TRASHED) {
            self::throwAnException($lead, self::TRASH);
        }
    }
}
