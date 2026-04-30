FROM node:20-alpine

WORKDIR /var/www/frontend

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ .

# Production: build static files
RUN npm run build

EXPOSE 5173
