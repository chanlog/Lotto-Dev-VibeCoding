# 🎯 로또 번호 분석 서비스

사주 기반 오늘의 운세 분석을 통한 로또 번호 추천 서비스입니다.

## 📋 프로젝트 개요

- **메인 기능**: 사주 기반 오늘의 운세 분석 → 로또 번호 추천
- **기술스택**: Vue.js (프론트엔드) + Laravel (백엔드) + Docker
- **개발 환경**: Docker 컨테이너 기반 개발/테스트/배포 환경 분리
- **테스트 전략**: TDD 방식으로 테스트 코드 먼저 작성 후 구현

## 🚀 빠른 시작

### 1. 프로젝트 클론 및 환경 설정

```bash
git clone <repository-url>
cd Lotto
cp .env.example .env
```

### 2. Docker 환경 시작

```bash
# 개발 환경 시작
docker-compose up -d

# 테스트 환경 시작
docker-compose -f docker-compose.test.yml up -d

# 프로덕션 환경 시작
docker-compose -f docker-compose.prod.yml up -d
```

### 3. 의존성 설치

```bash
# 백엔드 의존성 설치
docker-compose exec backend composer install

# 프론트엔드 의존성 설치
docker-compose exec frontend npm install
```

### 4. 데이터베이스 마이그레이션

```bash
# 마이그레이션 실행
docker-compose exec backend php artisan migrate

# 시드 데이터 생성
docker-compose exec backend php artisan db:seed
```

## 🧪 테스트 실행

```bash
# 전체 테스트 실행
./scripts/run-all-tests.sh

# 백엔드 테스트만 실행
docker-compose exec backend php artisan test

# 프론트엔드 테스트만 실행
docker-compose exec frontend npm run test
```

## 🏗️ 프로젝트 구조

```
Lotto/
├── frontend/                 # Vue.js 프론트엔드
│   ├── src/
│   │   ├── components/      # 재사용 가능한 컴포넌트
│   │   ├── views/          # 페이지 컴포넌트
│   │   ├── store/          # Vuex 상태 관리
│   │   ├── router/         # Vue Router 설정
│   │   ├── services/       # API 서비스
│   │   └── utils/          # 유틸리티 함수
│   ├── tests/              # 테스트 파일
│   └── Dockerfile          # 개발용 Dockerfile
├── backend/                 # Laravel 백엔드
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/         # Eloquent 모델
│   │   ├── Services/       # 비즈니스 로직
│   │   └── Repositories/   # 데이터 접근 계층
│   ├── database/
│   │   ├── migrations/     # 데이터베이스 마이그레이션
│   │   ├── seeders/        # 시드 데이터
│   │   └── factories/      # 모델 팩토리
│   ├── tests/              # 테스트 파일
│   └── Dockerfile          # 개발용 Dockerfile
├── scripts/                 # 유틸리티 스크립트
└── docker-compose.yml      # Docker 서비스 설정
```

## 🔧 개발 가이드

### 커밋 메시지 규칙

```
<타입>(<범위>): <내용>

타입:
* feat: 새로운 기능 추가
* fix: 버그 수정
* test: 테스트 코드 추가/수정
* refactor: 코드 리팩토링
* docs: 문서 관련 작업
* style: 포맷팅, 코드 스타일
* chore: 빌드, 설정 관련
* docker: Docker 관련 작업
```

### TDD 개발 프로세스

1. 기능 요구사항 정의
2. 테스트 케이스 작성 (실패)
3. 최소한의 코드로 테스트 통과
4. 리팩토링 및 최적화
5. 통합 테스트 실행

## 🌟 핵심 기능

### 1. 인터랙티브 메인페이지
- 최근 당첨번호 실시간 표시
- 번호별 출현 빈도 통계
- 상단 네비게이션 바

### 2. 사주 기반 운세 분석
- 생년월일, 태어난 시간 기반 분석
- 오늘의 운세 해석 (재물운, 행운지수)
- 운세 결과 기반 로또 번호 추천

### 3. 번호 생성 시스템
- 완전 자동: 랜덤 알고리즘 기반
- 반자동: 사용자 선호 번호 + 자동 생성
- 운세 기반: 사주 분석 결과 반영

### 4. 회원 시스템
- JWT 기반 인증
- 구매 기록 관리
- 당첨 결과 추적

### 5. 통계 대시보드
- 개인별 구매 통계
- 번호 패턴 분석
- 당첨 확률 시뮬레이션

## 🔗 API 엔드포인트

### 인증
- `POST /api/auth/register` - 회원가입
- `POST /api/auth/login` - 로그인
- `POST /api/auth/logout` - 로그아웃

### 로또
- `GET /api/lotto/latest` - 최신 당첨번호 조회
- `POST /api/lotto/generate` - 번호 생성
- `POST /api/lotto/save` - 번호 저장

### 사주 분석
- `POST /api/fortune/analyze` - 운세 분석
- `POST /api/fortune/generate-numbers` - 운세 기반 번호 생성

### 통계
- `GET /api/statistics/numbers` - 번호 통계
- `GET /api/statistics/patterns` - 패턴 분석

## 📊 성능 목표

- **응답 시간**: API 평균 응답 시간 < 200ms
- **테스트 커버리지**: 90% 이상
- **동시 접속자**: 1000명 이상 지원
- **가용성**: 99.9% 이상

## 🛡️ 보안 고려사항

- JWT 토큰 보안 설정
- API 요청 제한 (Rate Limiting)
- 사용자 입력 데이터 검증
- SQL 인젝션 방지
- XSS 공격 방지

## 🔄 배포 전략

- **스테이징**: 자동 배포 후 수동 검증
- **프로덕션**: 승인 후 자동 배포
- **롤백**: 문제 발생 시 즉시 이전 버전으로 복구

## 📞 지원 및 문의

- 개발 관련 문의: [개발자 연락처]
- 버그 리포트: GitHub Issues
- 기능 요청: GitHub Discussions

---

💡 **참고**: 이 프로젝트는 TDD 방식으로 개발되었으며, 모든 기능은 테스트 코드를 먼저 작성한 후 구현되었습니다.

## 🌟 개발 철학

이 프로젝트는 **"vibe coding"** 접근법을 통해 개발되었습니다. 직관적인 코드 작성과 창의적인 문제 해결을 중시하며, 사용자 경험을 최우선으로 하는 개발 문화를 지향합니다.