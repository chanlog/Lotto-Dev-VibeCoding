FROM node:18-alpine

# 작업 디렉토리 설정
WORKDIR /app

# 의존성 설치를 위한 package.json 복사
COPY package*.json ./

# 의존성 설치
RUN npm install

# 소스 코드 복사
COPY . .

# 개발 서버 시작
EXPOSE 3000
CMD ["npm", "run", "serve"]