# user-bundle
Бандл с пользователями

## Установка

Composer.json:
```json
{
    ...
      "repositories": [
        {
          "type": "vcs",
          "url": "https://github.com/chamelon-dev/user-bundle"
        }
      ]
   ...
}
```
Добавление бандла:
```bash
composer require chamelon-dev/user-bundle
```

#### Конфигурирование бандла:

##### config/routes.yaml

```yaml
logout:
  path: /logout

api_login_check:
  path: /api/login_check
```

##### config/packages/doctrine.yaml
    
```yaml
doctrine:
  orm:
    mappings:
      user_bundle:
        type: annotation
        is_bundle: false
        prefix: 'Pantheon\UserBundle\Entity'
        dir: "%kernel.root_dir%/../vendor/chamelon-dev/user-bundle/src/Entity"
        alias: NewsTop
```

##### config/packages/security.yaml

```yaml
security:
    access_decision_manager:
        strategy: affirmative
        allow_if_all_abstain: false
        
    encoders:
        Pantheon\UserBundle\Entity\User:
            algorithm: auto

    providers:
        app_user_provider:
            id: user_bundle.user.provider.entity

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        login:
          pattern: ^/api/login
          stateless: true
          json_login:
            check_path: /api/login_check
            success_handler: lexik_jwt_authentication.handler.authentication_success
            failure_handler: lexik_jwt_authentication.handler.authentication_failure
        api:
          pattern:   ^/api
          stateless: true
          guard:
            authenticators:
              - lexik_jwt_authentication.jwt_token_authenticator            
        main:
            access_denied_handler: user_bundle.handler.access_denied
            anonymous: lazy
            provider: app_user_provider
            logout:
                path:   /logout
                invalidate_session: true
            guard:
                authenticators:
                    - user_bundle.authenticator.login_form

    role_hierarchy:
        ROLE_ADMIN: [ROLE_USER]
    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api$,       roles: IS_AUTHENTICATED_FULLY }
        - { path: ^/, roles: IS_AUTHENTICATED_ANONYMOUSLY}
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/logout/redirect$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/profile, roles: ROLE_USER }
```

##### config/routes/user_bundle.yaml
```yaml
user_bundle:
  resource: ../../vendor/chamelon-dev/user-bundle/src/Controller/
  type: annotation
```

##### config/packages/framework.yaml
```yaml
framework:

    session:
        handler_id: session.handler.pdo
        cookie_secure: auto
        cookie_samesite: lax
        cookie_lifetime: 432000
        gc_maxlifetime: 432000
```

Настройка JWT-аутентификации:

##### .env
```yaml
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
```

##### .gitignore
```text
/config/jwt/*.pem
```

##### config/packages/lexik_jwt_authentication.yaml
```yaml
lexik_jwt_authentication:
  secret_key: '%env(resolve:JWT_SECRET_KEY)%'
  public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
  pass_phrase: '%env(JWT_PASSPHRASE)%'
  token_ttl: 3600
```

Создание миграции:
```bash
php bin/console make:migration
```
Правка и выполнение созданного файла миграции:
 ```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
 ```

Создание таблицы с сессиями пользователей (в миграциях этой таблицы нет):
 ```bash
php bin/console app:create-session-table
 ```

Применение фикстур:
 ```bash
php bin/console app:load-user-fixtures
 ```

После этого создается пользователь (superadmin:P@ssw0rd) с неограниченными правами. 

Сохранение пермишнов на основе аннотаций контроллеров:
 ```bash
php bin/console app:update-permissions
 ```

Генерация ключей для JWT-аутентификации:
 ```bash
php bin/console lexik:jwt:generate-keypair
 ```

Проверка получения токена:
 ```bash
curl -X POST -H "Content-Type: application/json" https://localhost/api/login_check -d '{"username":"superadmin","password":"P@ssw0rd"}'
 ```
