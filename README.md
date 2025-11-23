c2c/
├── app/
│   ├── Controllers/        # Các controller xử lý logic
│   ├── Models/             # Các model tương tác với database
│   ├── Views/              # Các file giao diện (HTML, Blade nếu dùng Laravel)
│   ├── Config/             # File cấu hình (DB, app settings)
│   ├── Routes/             # Định tuyến
│   └── Helpers/            # Hàm tiện ích
├── public/
│   ├── index.php           # Điểm vào chính của ứng dụng
│   ├── assets/             # CSS, JS, images
│   └── .htaccess           # Cấu hình Apache
├── vendor/                 # Thư viện Composer
├── .env                    # File môi trường (cấu hình DB, key)
├── composer.json           # File quản lý thư viện
└── README.md               # Hướng dẫn triển khai

DB_HOST=localhost
DB_NAME=c2c_marketplace
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8080

# Gói cần thiết trước khi chạy website ( vendor )
+ Composer install

# Cấu hình .env 
DB_HOST=localhost
DB_NAME=c2c_marketplace
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8080

# Cách Khởi chạy website 
# php run-websocket.php
# ctrl + ` 
# cd public
# php -S localhost:8080


# Xem các route 
# Routes/web.php

# Cấu hình database 
# Config/Database.php

# Cấu hình PayOS
# Config/payos ( 3 mã payos cung cấp)

