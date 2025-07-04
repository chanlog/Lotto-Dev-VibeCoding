# 로또 분석 서비스 - Claude 개발 가이드

## 🎯 프로젝트 개요
- **프로젝트명**: 로또 번호 분석 서비스
- **개발 방식**: TDD (Test-Driven Development)
- **기술 스택**: Vue.js + Laravel + Docker + MySQL + Redis
- **핵심 기능**: 사주 기반 운세 분석 → 로또 번호 추천

## 🚀 개발 환경 명령어

### Docker 환경 관리
```bash
# 개발 환경 시작
docker-compose up -d

# 테스트 환경 시작
docker-compose -f docker-compose.test.yml up -d

# 프로덕션 환경 시작
docker-compose -f docker-compose.prod.yml up -d

# 환경 정리
docker-compose down -v
```

### 테스트 실행
```bash
# 전체 테스트 실행
./scripts/run-all-tests.sh

# 백엔드 테스트 (Laravel)
docker-compose exec backend php artisan test
docker-compose exec backend php artisan test --coverage

# 프론트엔드 테스트 (Vue.js)
docker-compose exec frontend npm run test:unit
docker-compose exec frontend npm run test:e2e
```

### 개발 서버 접속
```bash
# 백엔드 컨테이너 접속
docker-compose exec backend bash

# 프론트엔드 컨테이너 접속
docker-compose exec frontend sh

# 데이터베이스 접속
docker-compose exec database mysql -u root -p
```

## 📋 개발 진행 상황

### ✅ 완료된 작업
1. **Docker 환경 구성**
   - 개발/테스트/프로덕션 환경 분리
   - MySQL, Redis 컨테이너 설정
   - 멀티스테이지 빌드 Dockerfile 구성

2. **Vue.js 프론트엔드 초기 설정**
   - Vue 3 + TypeScript 환경 구성
   - Tailwind CSS 스타일링 설정
   - Vue Router, Vuex 설정
   - Jest + Cypress 테스트 환경 구성

3. **Laravel 백엔드 초기 설정**
   - Laravel 10 + PHP 8.2 환경 구성
   - JWT 인증 시스템 설정
   - API 라우트 구조 설계
   - PHPUnit 테스트 환경 구성

4. **프로젝트 구조 및 기본 설정**
   - 폴더 구조 생성
   - 환경변수 설정
   - Git 설정 및 문서화

### 🔄 다음 단계 (우선순위별)
1. **프론트엔드 컴포넌트 개발** (테스트 우선)
   - 네비게이션 바 컴포넌트
   - 메인페이지 인터랙티브 요소
   - 로또 번호 표시 컴포넌트
   - 사주 입력 폼 컴포넌트

2. **백엔드 API 개발** (테스트 우선)
   - 인증 시스템 (JWT)
   - 로또 번호 생성 서비스
   - 사주 분석 서비스
   - 동행복권 API 연동

3. **데이터베이스 설계 및 구현**
   - 마이그레이션 파일 생성
   - 모델 및 관계 설정
   - 시드 데이터 생성

4. **통합 테스트 및 E2E 테스트**
   - API 통합 테스트
   - 사용자 플로우 E2E 테스트

## 🧪 TDD 개발 프로세스

### 1. 테스트 작성 규칙
- 기능 구현 전 항상 테스트 케이스 먼저 작성
- 테스트가 실패하는 것을 확인 후 구현 시작
- 최소한의 코드로 테스트 통과
- 리팩토링 후 테스트 재실행

### 2. 커밋 규칙
```bash
# 테스트 코드 작성
git commit -m "test(frontend): 네비게이션 바 컴포넌트 테스트 작성"

# 기능 구현
git commit -m "feat(frontend): 반응형 네비게이션 바 컴포넌트 구현"

# 테스트 추가
git commit -m "test(backend): 사주 분석 서비스 단위 테스트 추가"

# 서비스 구현
git commit -m "feat(backend): 사주 기반 운세 분석 서비스 구현"
```

### 3. 테스트 커버리지 목표
- 단위 테스트: 90% 이상
- 통합 테스트: 주요 API 엔드포인트 100%
- E2E 테스트: 핵심 사용자 플로우 커버

## 🛠️ 기술 스택 세부 정보

### Frontend (Vue.js)
- **Vue 3**: Composition API 사용
- **TypeScript**: 타입 안정성 확보
- **Tailwind CSS**: 유틸리티 퍼스트 CSS
- **Vue Router**: SPA 라우팅
- **Vuex**: 상태 관리
- **Axios**: HTTP 클라이언트
- **Chart.js**: 데이터 시각화
- **Vee-Validate**: 폼 유효성 검증

### Backend (Laravel)
- **Laravel 10**: PHP 웹 프레임워크
- **PHP 8.2**: 최신 PHP 버전
- **JWT**: 토큰 기반 인증
- **MySQL**: 관계형 데이터베이스
- **Redis**: 캐싱 및 세션 저장소
- **Guzzle**: HTTP 클라이언트 (외부 API 연동)
- **Laravel Horizon**: 큐 모니터링
- **Laravel Telescope**: 디버깅 도구

### Infrastructure
- **Docker**: 컨테이너화
- **Docker Compose**: 서비스 오케스트레이션
- **Nginx**: 프록시 서버 (프로덕션)
- **GitHub Actions**: CI/CD 파이프라인

## 🔧 개발 시 주의사항

### 1. 성능 최적화
- API 응답 캐싱 (Redis)
- 데이터베이스 쿼리 최적화
- 프론트엔드 번들 최적화

### 2. 보안 고려사항
- JWT 토큰 보안 설정
- API 요청 제한 (Rate Limiting)
- 사용자 입력 데이터 검증
- CORS 정책 설정

### 3. 테스트 품질 관리
- 테스트 코드 품질 유지
- 모킹 및 스텁 적절히 활용
- 테스트 데이터 격리

## 📚 참고 자료

### 프론트엔드
- [Vue.js 공식 문서](https://vuejs.org/)
- [Tailwind CSS 문서](https://tailwindcss.com/)
- [Vue Test Utils](https://test-utils.vuejs.org/)

### 백엔드
- [Laravel 공식 문서](https://laravel.com/docs)
- [PHPUnit 문서](https://phpunit.de/)
- [Laravel Testing](https://laravel.com/docs/10.x/testing)

### Docker
- [Docker 공식 문서](https://docs.docker.com/)
- [Docker Compose 문서](https://docs.docker.com/compose/)

---

⚠️ **중요**: 이 프로젝트는 TDD 방식으로 개발되므로, 모든 기능 구현 전에 반드시 테스트 코드를 먼저 작성해야 합니다.