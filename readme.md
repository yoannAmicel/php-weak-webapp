# Prerequisites

## Required Tools and Software

### MySQL Server, Apache Server, PHP, and PHPMyAdmin
- **Linux:** [Installation Guide](https://fluentsupport.com/phpmyadmin-installation/)
- **macOS:** [Installation Guide](https://fluentsupport.com/phpmyadmin-installation/)

<em>Login phpMyAdmin and click the "SQL" tab. Copy & paste in the prompt this file and click execute : [includes/sql/php_weak_webapp.sql](https://github.com/yoannAmicel/php-weak-webapp/blob/216958235c9f37d559dc337341d8310fb2d329b3/includes/sql/php_weak_webapp.sql)</em>

<em>Note that you also can import the file using "SQL" as file type.</em>

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

### Update `/etc/hosts`
Open your `/etc/hosts` file with admin privileges before adding these lines:
```/etc/apache2/sites-enabled/000-default.conf
::1 avenix.local www.avenix.local
```

### Update `php.ini`
Ensure the following settings are configured:
```ini
file_uploads = On
upload_max_filesize = 25M
post_max_size = 30M
```

### Create a dedicated vhost `/etc/apache2/sites-enabled/weak-php.conf`
Ensure to replace `path_to_local_projet` by your own path:
```/etc/apache2/sites-enabled/000-default.conf
<VirtualHost *:9998>
    ServerName avenix.local
    DocumentRoot "/path_to_local_projet/public"

    <Directory "/path_to_local_projet">
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "/path_to_local_projet/logs/error.log"
    CustomLog "/path_to_local_projet/logs/access.log" common
</VirtualHost>
```
<em>Don't forget to restart Apache afterwards.</em>

### Add an AV exclusion
For the attacks to proceed correctly, it is necessary to set up exceptions 
to the project "public" folder:
- <em>**/path_to_local_projet/public**</em>

---

## Useful commands (for Linux) :
- **PHP - restart server** : sudo systemctl restart php8.3-fpm
- **Apache - restart server** : sudo systemctl restart apache2
- **Vault - start server** : sudo vault server -config=/etc/vault/config.hcl
  - _**Vault - server endpoint** : https://127.0.0.1:8200/ui/vault/auth?with=token_