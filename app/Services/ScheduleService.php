<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    /**
     * Create default schedules for a venue (Monday to Sunday).
     * 
     * @param Venue $venue
     * @param array $scheduleData Array of schedules with day_of_week, open_time, close_time, is_closed
     * @return Collection
     */
    public function createSchedulesForVenue(Venue $venue, array $scheduleData): Collection
    {
        $createdSchedules = collect();

        foreach ($scheduleData as $schedule) {
            $createdSchedule = Schedule::create([
                'venue_id' => $venue->id,
                'day_of_week' => $schedule['day_of_week'],
                'open_time' => $schedule['open_time'],
                'close_time' => $schedule['close_time'],
                'is_closed' => $schedule['is_closed'] ?? false,
            ]);

            $createdSchedules->push($createdSchedule);
        }

        return $createdSchedules;
    }

    /**
     * Create default 24/7 schedule for a venue.
     * 
     * @param Venue $venue
     * @return Collection
     */
    public function createDefaultSchedule(Venue $venue): Collection
    {
        $defaultSchedules = [];

        // Create schedule for all 7 days (0 = Sunday, 6 = Saturday)
        for ($day = 0; $day <= 6; $day++) {
            $defaultSchedules[] = [
                'day_of_week' => $day,
                'open_time' => '09:00',
                'close_time' => '21:00',
                'is_closed' => false,
            ];
        }

        return $this->createSchedulesForVenue($venue, $defaultSchedules);
    }

    /**
     * Get available time periods for a specific day based on venue schedule.
     * 
     * @param Venue $venue
     * @param int $dayOfWeek Day of week (0 = Sunday, 6 = Saturday)
     * @return array Array of available time slots
     */
    public function getAvailableTimePeriods(Venue $venue, int $dayOfWeek): array
    {
        $schedule = $venue->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$schedule || $schedule->is_closed) {
            return [];
        }

        $bookingDuration = $venue->booking_duration_hours ?? 1;
        $bufferMinutes = $venue->buffer_minutes ?? 0;

        $openTime = Carbon::parse($schedule->open_time);
        $closeTime = Carbon::parse($schedule->close_time);

        $timeSlots = [];
        $currentTime = $openTime->copy();

        while ($currentTime->copy()->addHours($bookingDuration)->lte($closeTime)) {
            $slotEnd = $currentTime->copy()->addHours($bookingDuration);
            
            $timeSlots[] = [
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'duration_hours' => $bookingDuration,
            ];

            // Move to next slot with buffer
            $currentTime->addHours($bookingDuration)->addMinutes($bufferMinutes);
        }

        return $timeSlots;
    }

    /**
     * Get available time periods for all days of the week.
     * 
     * @param Venue $venue
     * @return array
     */
    public function getAllAvailableTimePeriods(Venue $venue): array
    {
        $allPeriods = [];
        $daysOfWeek = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        foreach ($daysOfWeek as $dayNumber => $dayName) {
            $schedule = $venue->schedules()->where('day_of_week', $dayNumber)->first();
            
            $allPeriods[$dayName] = [
                'day_of_week' => $dayNumber,
                'is_closed' => $schedule ? $schedule->is_closed : true,
                'open_time' => $schedule && !$schedule->is_closed ? $schedule->open_time : null,
                'close_time' => $schedule && !$schedule->is_closed ? $schedule->close_time : null,
                'available_slots' => $this->getAvailableTimePeriods($venue, $dayNumber),
            ];
        }

        return $allPeriods;
    }

    /**
     * Update or create schedules for a venue.
     * 
     * @param Venue $venue
     * @param array $scheduleData
     * @return Collection
     */
    public function updateSchedules(Venue $venue, array $scheduleData): Collection
    {
        $updatedSchedules = collect();

        foreach ($scheduleData as $schedule) {
            $updated = Schedule::updateOrCreate(
                [
                    'venue_id' => $venue->id,
                    'day_of_week' => $schedule['day_of_week'],
                ],
                [
                    'open_time' => $schedule['open_time'],
                    'close_time' => $schedule['close_time'],
                    'is_closed' => $schedule['is_closed'] ?? false,
                ]
            );

            $updatedSchedules->push($updated);
        }

        return $updatedSchedules;
    }

    /**
     * Validate that a booking time falls within available time slots.
     * 
     * @param Venue $venue
     * @param string $date Date in Y-m-d format
     * @param string $startTime Time in H:i format
     * @param int $durationHours
     * @return bool
     */
    public function isTimeSlotAvailable(Venue $venue, string $date, string $startTime, int $durationHours): bool
    {
        $bookingDate = Carbon::parse($date);
        $dayOfWeek = $bookingDate->dayOfWeek;

        $schedule = $venue->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$schedule || $schedule->is_closed) {
            return false;
        }

        $requestedStart = Carbon::parse($startTime);
        $requestedEnd = $requestedStart->copy()->addHours($durationHours);
        $openTime = Carbon::parse($schedule->open_time);
        $closeTime = Carbon::parse($schedule->close_time);

        // Check if requested time is within operating hours
        return $requestedStart->gte($openTime) && $requestedEnd->lte($closeTime);
    }
}
