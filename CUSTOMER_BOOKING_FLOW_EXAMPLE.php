<?php

/**
 * Customer Booking Flow Example
 * 
 * This demonstrates a complete booking flow using the scheduling feature
 */

echo "=== CUSTOMER BOOKING FLOW EXAMPLE ===\n\n";

// Step 1: Customer browses venues
echo "Step 1: Browse available venues\n";
echo "API: GET /api/customer/venues\n";
echo "----------------------------------------\n";
$venuesResponse = [
    'success' => true,
    'data' => [
        'data' => [
            [
                'id' => 1,
                'name' => 'Grand Wedding Hall',
                'base_price' => '500.00',
                'booking_duration_hours' => 4,
                'currency' => 'USD',
                'photos' => ['photo1.jpg'],
            ],
            [
                'id' => 2,
                'name' => 'Conference Room',
                'base_price' => '75.00',
                'booking_duration_hours' => 1,
                'currency' => 'USD',
                'photos' => ['photo2.jpg'],
            ],
        ]
    ]
];
echo json_encode($venuesResponse, JSON_PRETTY_PRINT) . "\n\n";

// Step 2: Customer selects a venue and views details
echo "Step 2: View venue details with available time periods\n";
echo "API: GET /api/customer/venues/1\n";
echo "----------------------------------------\n";
$venueDetailResponse = [
    'success' => true,
    'data' => [
        'venue' => [
            'id' => 1,
            'name' => 'Grand Wedding Hall',
            'description' => 'Perfect for weddings and large events',
            'base_price' => '500.00',
            'currency' => 'USD',
            'booking_duration_hours' => 4,
            'buffer_minutes' => 30,
            'capacity' => 200,
            'amenities' => ['WiFi', 'Parking', 'AC', 'Sound System'],
        ],
        'available_time_periods' => [
            'Saturday' => [
                'day_of_week' => 6,
                'is_closed' => false,
                'open_time' => '10:00:00',
                'close_time' => '22:00:00',
                'available_slots' => [
                    [
                        'start_time' => '10:00',
                        'end_time' => '14:00',
                        'duration_hours' => 4
                    ],
                    [
                        'start_time' => '14:30',
                        'end_time' => '18:30',
                        'duration_hours' => 4
                    ],
                    [
                        'start_time' => '19:00',
                        'end_time' => '23:00',
                        'duration_hours' => 4
                    ]
                ]
            ],
            'Sunday' => [
                'day_of_week' => 0,
                'is_closed' => false,
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'available_slots' => [
                    [
                        'start_time' => '10:00',
                        'end_time' => '14:00',
                        'duration_hours' => 4
                    ],
                    [
                        'start_time' => '14:30',
                        'end_time' => '18:30',
                        'duration_hours' => 4
                    ]
                ]
            ],
            'Monday' => [
                'day_of_week' => 1,
                'is_closed' => true,
                'open_time' => null,
                'close_time' => null,
                'available_slots' => []
            ]
        ]
    ]
];
echo json_encode($venueDetailResponse, JSON_PRETTY_PRINT) . "\n\n";

// Step 3: Customer selects date and time
echo "Step 3: Customer selects date and time slot\n";
echo "Customer Action: Selects Saturday, December 28, 2025 at 14:30-18:30\n";
echo "----------------------------------------\n";
$selectedSlot = [
    'date' => '2025-12-28',  // Saturday
    'start_time' => '14:30',
    'end_time' => '18:30',
    'duration_hours' => 4
];
echo "Selected Slot: " . json_encode($selectedSlot, JSON_PRETTY_PRINT) . "\n\n";

// Step 4: Customer creates booking (must be authenticated)
echo "Step 4: Create booking\n";
echo "API: POST /api/customer/bookings\n";
echo "Authorization: Bearer {customer_token}\n";
echo "----------------------------------------\n";
$bookingRequest = [
    'venue_id' => 1,
    'booking_date' => '2025-12-28',
    'start_time' => '14:30',
    'duration_hours' => 4,
    'number_of_guests' => 150,
    'notes' => 'Wedding reception for 150 guests'
];
echo "Request Body:\n";
echo json_encode($bookingRequest, JSON_PRETTY_PRINT) . "\n\n";

// Step 5: Booking created, proceed to payment
echo "Step 5: Booking created, proceed to payment\n";
echo "API Response from POST /api/customer/bookings\n";
echo "----------------------------------------\n";
$bookingResponse = [
    'success' => true,
    'message' => 'Booking created successfully',
    'data' => [
        'booking' => [
            'id' => 123,
            'venue_id' => 1,
            'customer_id' => 5,
            'booking_date' => '2025-12-28',
            'start_time' => '14:30:00',
            'end_time' => '18:30:00',
            'duration_hours' => 4,
            'number_of_guests' => 150,
            'total_price' => '2000.00',  // base_price * duration_hours
            'currency' => 'USD',
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => '2025-12-23T20:00:00.000000Z'
        ]
    ]
];
echo json_encode($bookingResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "Step 6: Initiate payment\n";
echo "API: POST /api/customer/payments/bookings/123/initiate\n";
echo "----------------------------------------\n";
echo "(Payment processing continues...)\n\n";

echo "=== BOOKING FLOW COMPLETE ===\n\n";

// Frontend JavaScript Example
echo "=== FRONTEND IMPLEMENTATION EXAMPLE (JavaScript/React) ===\n\n";

$frontendExample = <<<'JAVASCRIPT'
// React Component Example for Venue Booking

import React, { useState, useEffect } from 'react';

function VenueBooking({ venueId }) {
  const [venue, setVenue] = useState(null);
  const [timePeriods, setTimePeriods] = useState(null);
  const [selectedDay, setSelectedDay] = useState('');
  const [selectedSlot, setSelectedSlot] = useState(null);

  useEffect(() => {
    // Fetch venue details with available time periods
    fetch(`/api/customer/venues/${venueId}`)
      .then(res => res.json())
      .then(data => {
        setVenue(data.data.venue);
        setTimePeriods(data.data.available_time_periods);
      });
  }, [venueId]);

  const handleDaySelect = (day) => {
    setSelectedDay(day);
    setSelectedSlot(null);
  };

  const handleSlotSelect = (slot) => {
    setSelectedSlot(slot);
  };

  const handleBooking = async () => {
    if (!selectedSlot || !selectedDay) {
      alert('Please select a day and time slot');
      return;
    }

    const bookingData = {
      venue_id: venueId,
      booking_date: '2025-12-28', // Calculate based on selectedDay
      start_time: selectedSlot.start_time,
      duration_hours: selectedSlot.duration_hours,
      number_of_guests: 100 // From form input
    };

    const response = await fetch('/api/customer/bookings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      body: JSON.stringify(bookingData)
    });

    const result = await response.json();
    if (result.success) {
      // Redirect to payment
      window.location.href = `/payment/${result.data.booking.id}`;
    }
  };

  if (!venue || !timePeriods) return <div>Loading...</div>;

  return (
    <div className="venue-booking">
      <h1>{venue.name}</h1>
      <p>{venue.description}</p>
      <p>Price: ${venue.base_price} per {venue.booking_duration_hours} hour(s)</p>

      <h2>Select a Day</h2>
      <div className="day-selector">
        {Object.entries(timePeriods).map(([day, schedule]) => (
          <button
            key={day}
            onClick={() => handleDaySelect(day)}
            disabled={schedule.is_closed}
            className={selectedDay === day ? 'selected' : ''}
          >
            {day}
            {schedule.is_closed && ' (Closed)'}
          </button>
        ))}
      </div>

      {selectedDay && !timePeriods[selectedDay].is_closed && (
        <>
          <h2>Available Time Slots for {selectedDay}</h2>
          <p>
            Open: {timePeriods[selectedDay].open_time} - 
            {timePeriods[selectedDay].close_time}
          </p>
          <div className="time-slots">
            {timePeriods[selectedDay].available_slots.map((slot, index) => (
              <button
                key={index}
                onClick={() => handleSlotSelect(slot)}
                className={selectedSlot === slot ? 'selected' : ''}
              >
                {slot.start_time} - {slot.end_time}
                <br />
                ({slot.duration_hours} hours)
              </button>
            ))}
          </div>
        </>
      )}

      {selectedSlot && (
        <div className="booking-summary">
          <h3>Booking Summary</h3>
          <p>Venue: {venue.name}</p>
          <p>Day: {selectedDay}</p>
          <p>Time: {selectedSlot.start_time} - {selectedSlot.end_time}</p>
          <p>Duration: {selectedSlot.duration_hours} hours</p>
          <p>Total: ${venue.base_price * selectedSlot.duration_hours}</p>
          <button onClick={handleBooking} className="btn-primary">
            Proceed to Book
          </button>
        </div>
      )}
    </div>
  );
}

export default VenueBooking;
JAVASCRIPT;

echo $frontendExample . "\n\n";

echo "=== KEY POINTS FOR FRONTEND DEVELOPERS ===\n";
echo "1. Available time periods are automatically included when viewing a venue\n";
echo "2. Days are named: Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday\n";
echo "3. Closed days have empty available_slots array\n";
echo "4. Each slot includes start_time, end_time, and duration_hours\n";
echo "5. Use the start_time when creating a booking\n";
echo "6. Calculate total price: base_price × duration_hours\n";
echo "7. All venue endpoints are public (no auth needed for browsing)\n";
echo "8. Authentication only required when creating a booking\n\n";
