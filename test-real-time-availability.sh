#!/bin/bash

# Test script for Real-Time Availability Feature
# This demonstrates the difference between with/without date parameter

BASE_URL="http://localhost:8000/api/customer"
VENUE_ID=1
TEST_DATE="2025-12-28"

echo "=========================================="
echo "Real-Time Availability Test Script"
echo "=========================================="
echo ""

echo "🔹 Test 1: Get Weekly Schedule (No Date)"
echo "GET ${BASE_URL}/venues/${VENUE_ID}/available-time-periods"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}/available-time-periods" \
  -H "Accept: application/json" | jq '{
    venue_id: .data.venue_id,
    date: .data.date,
    days: .data.available_time_periods | keys,
    monday_slots: .data.available_time_periods.Monday.available_slots | length
  }'
echo ""
echo ""

echo "🔹 Test 2: Get Real Availability for Specific Date"
echo "GET ${BASE_URL}/venues/${VENUE_ID}/available-time-periods?date=${TEST_DATE}"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}/available-time-periods?date=${TEST_DATE}" \
  -H "Accept: application/json" | jq '{
    venue_id: .data.venue_id,
    date: .data.date,
    day_name: .data.available_time_periods.day_name,
    total_slots: (.data.available_time_periods.available_slots | length),
    available_count: (.data.available_time_periods.available_slots | map(select(.is_available == true)) | length),
    booked_count: (.data.available_time_periods.booked_slots | length),
    slots: .data.available_time_periods.available_slots
  }'
echo ""
echo ""

echo "🔹 Test 3: View Venue with Specific Date"
echo "GET ${BASE_URL}/venues/${VENUE_ID}?date=${TEST_DATE}"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}?date=${TEST_DATE}" \
  -H "Accept: application/json" | jq '{
    venue_name: .data.venue.name,
    booking_duration_hours: .data.venue.booking_duration_hours,
    availability: {
      date: .data.available_time_periods.date,
      day_name: .data.available_time_periods.day_name,
      available_slots: (.data.available_time_periods.available_slots | map(select(.is_available == true)) | length),
      booked_slots: (.data.available_time_periods.booked_slots | length)
    }
  }'
echo ""
echo ""

echo "🔹 Test 4: Invalid Date Format"
echo "GET ${BASE_URL}/venues/${VENUE_ID}/available-time-periods?date=12/28/2025"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}/available-time-periods?date=12/28/2025" \
  -H "Accept: application/json" | jq '.'
echo ""
echo ""

echo "🔹 Test 5: Compare Multiple Dates (Today, Tomorrow, Day After)"
echo "------------------------------------------"
for i in 0 1 2; do
  if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    DATE=$(date -v+${i}d +%Y-%m-%d)
  else
    # Linux
    DATE=$(date -d "+${i} days" +%Y-%m-%d)
  fi
  
  echo "Date: $DATE"
  curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}/available-time-periods?date=${DATE}" \
    -H "Accept: application/json" | jq -r '
      "  Day: \(.data.available_time_periods.day_name)",
      "  Total Slots: \(.data.available_time_periods.available_slots | length)",
      "  Available: \(.data.available_time_periods.available_slots | map(select(.is_available == true)) | length)",
      "  Booked: \(.data.available_time_periods.booked_slots | length)"
    '
  echo ""
done

echo "=========================================="
echo "✅ Tests completed!"
echo "=========================================="
echo ""
echo "📝 Key Observations:"
echo "• Without date: Returns all 7 days"
echo "• With date: Returns only that specific day"
echo "• Slots marked with is_available: true/false"
echo "• Booked slots listed separately"
echo "• Invalid date format returns 422 error"
