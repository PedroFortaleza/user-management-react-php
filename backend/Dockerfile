FROM php:8.3-cli-alpine
WORKDIR /app
COPY . .
EXPOSE 8000
CMD ["sh", "-c", "php seed.php && php -S 0.0.0.0:8000 -t public"]
