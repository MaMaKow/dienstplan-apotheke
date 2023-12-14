<?php

/*
 * Copyright (C) 2023 Mandelkow
 *
 * Dienstplan Apotheke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Roster;

/**
 * Class absence
 * @package PDR\Roster
 * @author Mandelkow
 */
class Absence implements \JsonSerializable {

    /**
     * @var int primary key of an \employee
     */
    private $employeeKey;

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var int The number of working days
     */
    private $days;

    /**
     * @var int
     */
    private $reasonId;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     * Possible values: 'approved', 'not_yet_approved', 'disapproved', 'changed_after_approval'
     */
    private $approval;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var \DateTime
     */
    private $timeStamp;

    public function __construct(
            int $employeeKey,
            \DateTime $start,
            \DateTime $end,
            int $days,
            int $reasonId,
            ?string $comment,
            string $approval,
            string $userName,
            \DateTime $timeStamp,
    ) {
        $this->employeeKey = $employeeKey;
        $this->start = $start;
        $this->end = $end;
        $this->days = $days;
        $this->reasonId = $reasonId;
        $this->comment = $comment;
        $this->approval = $approval;
        $this->userName = $userName;
        $this->timeStamp = $timeStamp;
    }

    /**
     * Get the employee key
     * @return int
     */
    public function getEmployeeKey(): int {
        return $this->employeeKey;
    }

    /**
     * Get the start date
     * @return \DateTime
     */
    public function getStart(): \DateTime {
        return $this->start;
    }

    /**
     * Get the end date
     * @return \DateTime
     */
    public function getEnd(): \DateTime {
        return $this->end;
    }

    /**
     * Get the number of working days
     * @return int
     */
    public function getDays(): int {
        return $this->days;
    }

    /**
     * Get the reason ID
     * @return int
     */
    public function getReasonId(): int {
        return $this->reasonId;
    }

    /**
     * Get the comment
     * @return string
     */
    public function getComment(): ?string {
        return $this->comment;
    }

    /**
     * Get the user name of the user who created this absence.
     * @return string
     */
    public function getUserName(): string {
        return $this->userName;
    }

    /**
     * Get the approval status
     * @return string
     */
    public function getApproval(): string {
        return $this->approval;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return mixed
     */
    public function jsonSerialize() {
        return [
            'employeeKey' => $this->employeeKey,
            'start' => $this->start->format('Y-m-d H:i:s'),
            'end' => $this->end->format('Y-m-d H:i:s'),
            'days' => $this->days,
            'reasonId' => $this->reasonId,
            'comment' => $this->comment,
            'userName' => $this->userName,
            'approval' => $this->approval,
        ];
    }
}
