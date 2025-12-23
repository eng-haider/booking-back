#!/bin/bash

# Test script for Customer Venue Scheduling API
# Make sure to replace {venue_id} with an actual venue ID from your database

BASE_URL="http://localhost:8000/api/customer"
VENUE_ID=1

echo "=========================================="
echo "Customer Venue Scheduling API Tests"
echo "=========================================="
echo ""

echo "Test 1: Get venue with available time periods"
echo "GET ${BASE_URL}/venues/${VENUE_ID}"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}" \
  -H "Accept: application/json" | jq '.data.available_time_periods'
echo ""
echo ""

echo "Test 2: Get only available time periods"
echo "GET ${BASE_URL}/venues/${VENUE_ID}/available-time-periods"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/${VENUE_ID}/available-time-periods" \
  -H "Accept: application/json" | jq '.'
echo ""
echo ""

echo "Test 3: Browse venues with schedules"
echo "GET ${BASE_URL}/venues?include=schedules"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues?include=schedules&per_page=2" \
  -H "Accept: application/json" | jq '.data.data[0] | {id, name, booking_duration_hours, schedules}'
echo ""
echo ""

echo "Test 4: Search venues with schedules"
echo "GET ${BASE_URL}/venues/search?query=hall&include=schedules"
echo "------------------------------------------"
curl -s -X GET "${BASE_URL}/venues/search?query=hall" \
  -H "Accept: application/json" | jq '.data.data[] | {id, name, booking_duration_hours}'
echo ""
echo ""

echo "=========================================="
echo "Tests completed!"
echo "=========================================="
