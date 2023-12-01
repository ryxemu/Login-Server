# Stage 1: Go Environment
FROM golang:alpine AS go-builder

WORKDIR /go/src/app

# Copy Go files
COPY main.go .
COPY register.go .

# Build Go binary
RUN go build -o main .

# Stage 2: PHP Environment
FROM php:7.4-alpine

WORKDIR /app

# Install necessary PHP extensions and SQLite
RUN docker-php-ext-install pdo pdo_sqlite \
    && apk add --no-cache sqlite

# Copy PHP files
COPY register.php .
COPY test.db .

# Copy SSL certificates
COPY cert.pem .
COPY key.pem .

# Copy Go binary from the previous stage
COPY --from=go-builder /go/src/app/main .

EXPOSE 443

CMD ["php", "-S", "0.0.0.0:443", "--docroot", ".", "--server", "cert.pem", "key.pem", "-t", "."]
