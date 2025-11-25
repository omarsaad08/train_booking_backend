#!/bin/bash
echo "Stopping containers..."
sudo docker compose down

echo "Rebuilding containers..."
sudo docker compose up -d --build

echo "Waiting for MySQL to start..."
sleep 15


echo "Testing API..."
curl -X GET http://localhost:8080/