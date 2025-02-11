# Prerequisites

## Required Tools and Software

### MySQL Server, Apache Server, PHP, and PHPMyAdmin
- **Linux:** [Installation Guide](https://fluentsupport.com/phpmyadmin-installation/)
- **macOS:** [Installation Guide](https://fluentsupport.com/phpmyadmin-installation/)

### Composer
- **Linux:** [Installation Guide](https://getcomposer.org/download/)
- **macOS:** `brew install composer`

### Git
- **Linux:** [Installation Guide](https://docs.github.com/en/get-started/getting-started-with-git/set-up-git)
- **macOS:** [Installation Guide](https://www.freecodecamp.org/news/setup-git-on-mac/)

### Vault
- **Linux:** [Installation Guide](https://developer.hashicorp.com/vault/install?product_intent=vault)
- **macOS:** [Installation Guide](https://developer.hashicorp.com/vault/tutorials/get-started/install-binary)

---

## Apache Configuration Prerequisites

### Update `php.ini`
Ensure the following settings are configured:
```ini
file_uploads = On
upload_max_filesize = 25M
post_max_size = 30M
```

### Update `/etc/apache2/sites-enabled/000-default.conf`
Ensure to replace `path_to_local_projet` by your own path:
```/etc/apache2/sites-enabled/000-default.conf
<VirtualHost *:80>
    ServerName 127.0.0.1
    DocumentRoot "/path_to_local_projet/public"

    <Directory "/path_to_local_projet">
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "/path_to_local_projet/logs/error.log"
    CustomLog "/path_to_local_projet/logs/access.log" common
</VirtualHost>
```
---

## Useful commands (for Linux) :
- **PHP - restart server** : sudo systemctl restart php8.3-fpm
- **Apache - restart server** : sudo systemctl restart apache2
- **Vault - start server** : sudo vault server -config=/etc/vault/config.hcl
  - _**Vault - server endpoint** : https://127.0.0.1:8200/ui/vault/auth?with=token_
