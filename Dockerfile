FROM php:8.3-cli

# ติดตั้งแพ็กเกจที่จำเป็นสำหรับ Laravel, SQLite และ Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm

# ติดตั้ง PHP Extensions
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ตั้งค่า Directory การทำงาน
WORKDIR /app

# คัดลอกไฟล์ทั้งหมดในโปรเจกต์เข้าไปใน Docker
COPY . .

# ติดตั้งไลบรารีของ Laravel และ Node.js (Vite)
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# เตรียมไฟล์ฐานข้อมูล SQLite
RUN mkdir -p database && touch database/database.sqlite

# คำสั่งรัน Server เมื่อ Deploy เสร็จ
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}