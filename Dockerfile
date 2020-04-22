FROM lefuturiste/php:7.4
ADD ./ /app
RUN composer install
CMD ["composer", "run", "test"]