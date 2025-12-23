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
     * @param string|null $date Specific date to check bookings (Y-m-d format)
     * @return array
     */
    public function getAllAvailableTimePeriods(Venue $venue, ?string $date = null): array
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

        // If a specific date is provided, filter by that date's availability
        if ($date) {
            $dateCarbon = Carbon::parse($date);
            $dayOfWeek = $dateCarbon->dayOfWeek;
            $dayName = $daysOfWeek[$dayOfWeek];

            // Get booked slots for this date
            $bookedSlots = $this->getBookedSlotsForDate($venue, $date);

            // Filter only the requested day and mark booked slots
            return [
                'date' => $date,
                'day_name' => $dayName,
                'day_of_week' => $dayOfWeek,
                'is_closed' => $allPeriods[$dayName]['is_closed'],
                'open_time' => $allPeriods[$dayName]['open_time'],
                'close_time' => $allPeriods[$dayName]['close_time'],
                'available_slots' => $this->filterBookedSlots(
                    $allPeriods[$dayName]['available_slots'],
                    $bookedSlots
                ),
                'booked_slots' => $bookedSlots,
            ];
        }

        return $allPeriods;
    }

    /**
     * Get booked time slots for a specific date.
     * 
     * @param Venue $venue
     * @param string $date
     * @return array
     */
    private function getBookedSlotsForDate(Venue $venue, string $date): array
    {
        $bookings = $venue->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $bookedSlots = [];
        foreach ($bookings as $booking) {
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);

            $bookedSlots[] = [
                'booking_id' => $booking->id,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'status' => $booking->status,
            ];
        }

        return $bookedSlots;
    }

    /**
     * Filter out booked slots from available slots.
     * 
     * @param array $availableSlots
     * @param array $bookedSlots
     * @return array
     */
    private function filterBookedSlots(array $availableSlots, array $bookedSlots): array
    {
        if (empty($bookedSlots)) {
            // Mark all as available
            return array_map(function ($slot) {
                $slot['is_available'] = true;
                return $slot;
            }, $availableSlots);
        }

        return array_map(function ($slot) use ($bookedSlots) {
            $slotStart = Carbon::parse($slot['start_time']);
            $slotEnd = Carbon::parse($slot['end_time']);

            // Check if this slot conflicts with any booking
            $isAvailable = true;
            foreach ($bookedSlots as $booked) {
                $bookedStart = Carbon::parse($booked['start_time']);
                $bookedEnd = Carbon::parse($booked['end_time']);

                // Check for time overlap
                if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            $slot['is_available'] = $isAvailable;
            return $slot;
        }, $availableSlots);
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
