🚀 Pterodactyl Theme Deployment Guide
This guide explains how to deploy Pterodactyl Panel (v1.15.2) and Custom Wings (v1.13.4) with the Arix Reverse Proxy feature, using the GitHub repository as the source.

📋 Prerequisites

· Fresh VPS (Ubuntu 22.04 / 24.04).
· Domain & Subdomain (e.g., panel.domain.com & node.domain.com).
· Root SSH access.
· Git installed (apt install git).
· Do not forget to set your Cloudflare DNS to DNS Only (Grey Cloud) for the node & proxy domains during setup.

---

🚀 PHASE 1: Install Base Panel Pterodactyl

1. Run Official Installer

```bash
bash <(curl -s https://pterodactyl-installer.se)
```

· Select 1 (Install Panel).
· Follow the prompts (domain, email, database setup, admin user).

2. Clear Default Files

```bash
# Stop Nginx
systemctl stop nginx

# Remove default panel folder
rm -rf /var/www/pterodactyl
```

3. Clone Custom Panel from GitHub

```bash
cd /var/www
git clone https://github.com/Gaeuly/pterodactyl-theme.git pterodactyl
cd pterodactyl

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Setup Environment
cp .env.example .env
php artisan key:generate
```

(Edit file .env using nano .env to match your database credentials).

4. Restore Permissions & Build

```bash
chown -R www-data:www-data /var/www/pterodactyl/*
chmod -R 755 /var/www/pterodactyl/storage /var/www/pterodactyl/bootstrap/cache

# Migrate database & Build React
php artisan migrate --force
yarn install
yarn build:production
php artisan optimize:clear

# Start Nginx
systemctl start nginx
```

---

🛠️ PHASE 2: Compile Custom Wings (Go)

1. Install Go & Dependencies

```bash
apt update && apt install -y golang git
```

2. Clone Wings Source v1.13.4

```bash
cd ~
git clone https://github.com/pterodactyl/wings.git
cd wings
git checkout v1.13.4
```

3. Inject Custom Proxy Files
Copy the modified files from your backup or the theme repo into the Wings source:

· router_server_proxy.go & router.go ➡️ ~/wings/router/
· root.go ➡️ ~/wings/cmd/

(You can download these files from the wings/ folder in your GitHub repo).

4. Compile Wings

```bash
# Fetch new dependencies (Lego for SSL)
go mod tidy

# Compile the binary
go build -o wings -v
```

5. Install Custom Wings

```bash
# Stop old Wings
systemctl stop wings

# Copy new binary
cp ~/wings/wings /usr/local/bin/wings
chmod +x /usr/local/bin/wings
```

---

⚙️ PHASE 3: Setup Wings (Node)

1. Panel Configuration
Go to Admin > Nodes > Create Node:

· FQDN: node.domain.com
· Communicate Over SSL: Use SSL Connection
· Daemon Port: 8080
· Behind Proxy: Not Behind Proxy (Cloudflare DNS Only)

2. Generate Token & Configure
Click Generate Token in Panel, then run:

```bash
cd /etc/pterodactyl
sudo wings configure --panel-url https://panel.domain.com --token [YOUR_TOKEN_HERE]
```

3. Setup SSL for Wings

```bash
systemctl stop nginx
certbot certonly --standalone -d node.domain.com
systemctl start nginx
```

4. Start Wings

```bash
systemctl start wings
systemctl status wings
```