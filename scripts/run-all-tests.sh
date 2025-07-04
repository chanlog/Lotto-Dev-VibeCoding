#!/bin/bash

echo "🧪 전체 테스트 실행 시작..."

# 컨테이너 실행 상태 확인
if ! docker-compose ps | grep -q "Up"; then
    echo "🚀 테스트 환경 시작..."
    docker-compose -f docker-compose.test.yml up -d
    sleep 30
fi

# 백엔드 테스트 실행
echo "🔧 백엔드 테스트 실행..."
docker-compose -f docker-compose.test.yml exec test-backend php artisan test --coverage

# 프론트엔드 테스트 실행
echo "🎨 프론트엔드 테스트 실행..."
docker-compose -f docker-compose.test.yml exec test-frontend npm run test:unit

# E2E 테스트 실행
echo "🎭 E2E 테스트 실행..."
docker-compose -f docker-compose.test.yml exec test-frontend npm run test:e2e

# 테스트 환경 정리
echo "🧹 테스트 환경 정리..."
docker-compose -f docker-compose.test.yml down

echo "✅ 전체 테스트 완료!"